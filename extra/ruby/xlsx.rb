# coding: UTF-8

require 'net/http'

class Xlsx
  attr_reader :axlsx
  
  MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  REQUEST_URL = 'http://fortnet/slim/'
  
  COLS = ["Дата", "ФИО", "Приход", "Уход", "Разница", "Обед", "Итог  ", "Норма", "Зарплата"]
  SUM = "Сумма"
  
  def initialize(data)
    counter = 1
    
    @axlsx = Axlsx::Package.new do |p|
      p.workbook.add_worksheet do |sheet|
        sheet.add_row COLS
        counter += 1
        data.each do |person, rows|
          first_row_for_person = counter
          sheet.add_row info_row(person, first_row_for_person)
          counter += 1
          rows.each do |row|
            sheet.add_row calc_row(row, first_row_for_person, counter)
            counter += 1
          end
          sheet.add_row calc_sum_row(person, first_row_for_person, counter-1)
          add_styles(sheet, first_row_for_person, counter)
          counter += 1
        end
        sheet.auto_filter = "A1:H#{counter-1}"
      end
    end
  end
  
  def result
    @axlsx.to_stream.read
  end
  
  def self.load(filename)
    url = URI("#{REQUEST_URL}#{CGI::escape filename}")
    response = Net::HTTP.get(url)
    begin
      res = JSON.parse(response)
      res = res['visits']
    rescue => e
      raise "#{e}\n<br>not valid data\n<br>#{response}"
      return
    end
    xlsx = Xlsx.new(res)
  end
  
  protected
  # calculated cells
  
  def info_row(person, c)
    ['', person, 'Оклад:', '0', 'Рубли/час:', "=IFERROR(D#{c}/22/8, 0)", '', '', '']
  end
  
  def calc_row(row, first_row_for_person, counter)
    values = row.dup
    values << calc_diff(counter)
    values << calc_rest(counter)
    values << calc_res(counter)
    values << '="8:00:00"-"0"'
    values << calc_salary(first_row_for_person, counter)
    values
  end
  
  def calc_sum_row(person, from, to)
    ['', person, '', '', '', SUM, calc_sum('G', from, to), calc_sum('H', from, to), calc_sum('I', from, to)]
  end
  
  def calc_diff(counter)
    a = "D#{counter}"
    b = "C#{counter}"
    "=IF(OR(LEN(#{a})=0, LEN(#{b})=0), \"\", #{a}-#{b})"
  end
  
  def calc_rest(counter)
    a = "E#{counter}"
    "=IFERROR(IF(LEN(#{a})=0, \"\", IF(HOUR(#{a}) >= 1, \"1\", \"0\") & \":00:00\"), \"\")"
  end
  
  def calc_res(counter)
    a = "E#{counter}"
    b = "F#{counter}"
    "=IF(OR(LEN(#{a}) = 0, LEN(#{b}) = 0), \"\", #{a}-#{b})"
  end
  
  def calc_sum(col, from, to)
    "=SUM(#{col}#{from}:#{col}#{to})"
  end
  
  def calc_salary(first_row_for_person, counter)
    base = "$F$#{first_row_for_person}"
    time = "G#{counter}"
    "=IF(OR(ISBLANK(#{time}),#{time}=\"\"),\"\",#{base}*(HOUR(#{time})+MINUTE(#{time})/60+SECOND(#{time})/3600))"
  end
  
  # styling
  
  def style(sheet, options)
    sheet.styles.add_style({border: {style: :thin, color: 'FF888888'}}.merge(options))
  end
  
  def rand2(from, to)
    rand(to - from) + from
  end
  
  def random_color
    'FF' + (rand2(150, 255) << 16 | rand2(150, 255) << 8 | rand2(150, 255)).to_s(16).upcase
  end
  
  def add_styles(sheet, from, to)
    color = random_color
    
    only_color = style(sheet, bg_color: color)
    sheet["A#{from}:B#{to}"].each { |c| c.style = only_color }
    sheet["C#{to}:F#{to}"].each { |c| c.style = only_color }
    sheet["C#{from}:H#{from}"].each { |c| c.style = only_color }
    
    color_and_time = style(sheet, bg_color: color, format_code: '[h]:mm:ss')
    sheet["C#{from+1}:H#{to-1}"].each { |c| c.style = color_and_time }
    sheet["G#{to}:H#{to}"].each { |c| c.style = color_and_time }
    
    money = style(sheet, bg_color: color, format_code: '#,##0.00&quot;р&quot;\.')
    sheet["I#{from}:I#{to}"].each { |c| c.style = money }
  end
end
