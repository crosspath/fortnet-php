<!DOCTYPE html>
<html>
  <body>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
      <textarea style="width: 100%; height: 400px; padding-bottom: 20px;" autofocus name="q"><?php echo htmlspecialchars($q); ?></textarea>
      <div style="position: relative; top: -30px; margin-bottom: -30px;"><input type="submit" value="Send"></div>
    </form>
    <p>Result:<?php if (is_array($result)) echo ' ' . $h -> pluralize_count(count($result), 'row'); ?></p>
    <div style="background-color: #eee; width: 100%;">
      <div style="max-height: 200px; padding: 20px; overflow: auto;">
        <?php echo $h -> dbi_result($result); ?>
      </div>
    </div>
  </body>
</html>
