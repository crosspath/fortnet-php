<?php $h = new Helper(); ?>
<html>
<head>
  <title>Система пропусков</title>
  <link rel="stylesheet" href="static/css/enter.css">
  <script type="text/javascript" src="static/js/zepto.min.js"></script>
  <script type="text/javascript" src="static/js/enters.js"></script>
</head>
<body>
  <div id="top">
    <form class="center" method="get" action=".">
      <span>Фильтр</span>
      <?php echo $h -> select($people, $filter['person'], 'name="person"'); ?>
      <input type="date" name="date_start" value="<?php echo $filter['date_start']; ?>">
      <span>&ndash;</span>
      <input type="date" name="date_end" value="<?php echo $filter['date_end']; ?>">
      <input type="submit" value="Применить">
    </form>
  </div>
  <div id="body">
    <h1>Регистрация входов и выходов по пропуску</h1>
    <a href="<?php echo $h -> export($fact_filter); ?>" class="export" title="Excel"></a>
    <?php foreach ($visits as $day => $dpeople): ?>
    <div class="day">
      <h2><span><?php echo $day; ?></span></h2>
      <div class="people">
        <?php foreach ($dpeople as $person => $rows): ?>
        <div class="person">
          <h3><span><?php echo $person; ?></span></h3>
          <table class="visits">
            <thead><th>Время</th><!--th>Статус</th><th colspan="2">Датчик</th--></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
              <tr>
                <td><?php echo $h -> extract_time($row['DATETIME']); ?></td>
                <?php /*<td>echo implode('</td><td>', $h -> extract_status($row['STATUS']));</td>*/ ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <p>Рабочее время: <span class="hl"><?php echo $h -> work_time($rows); ?></span></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</body>
</html>