Нужен gem 'axslx'.

В контроллере:

    class ExportController < ApplicationController
      def xlsx
        param = params[:param] + '.xlsx'
        xlsx = Xlsx.load(param)
        respond_to do |format|
          format.xlsx { send_data xlsx.result, filename: param, type: Xlsx::MIME }
        end
      end
    end

В маршрутах:

    get '/export/:param' => 'export#xlsx'

В initializer:

    require_relative 'app/models/xlsx.rb' # поправьте путь согласно согласно структуре папок вашего проекта
    Mime::Type.register Xlsx::MIME, :xlsx

Скопируйте файл xlsx.rb, например, в app/models/xlsx.rb, и измените в нём значение REQUEST_URL.
