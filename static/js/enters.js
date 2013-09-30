$(
  function()
  {
    $('h2 span').on('click', function(e)
      {
        $(e.target).parents('.day').find('.people').toggle();
        $(e.target).toggleClass('reduced');
      }
    );
    $('h3 span').on('click', function(e)
      {
        $(e.target).parents('.person').find('table, p').toggle();
        $(e.target).toggleClass('reduced');
      }
    );
  }
);
