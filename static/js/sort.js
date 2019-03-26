//jshint esversion:6
window.onload = function () {
    $('a').click(function(e) {
      let iter = 1;
      let oldValue;

      /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
      * Set variables: Table name where sorting should happen, that table rows *
      * and that table pressed column index.                                   *
      * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
      let column = e.currentTarget.offsetParent.cellIndex;
      let tableId = $(this).closest('table').attr('id');
      let table = $('#' + tableId).find('> tbody > tr');
      let that = $(this);

      /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
      * Add/remove class for column header so it would know how previously it  *
      * was sorted and it would know how and sort it other way around. First   *
      * time always sorts in descending order.                                 *
      * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
      if (that.hasClass("ascending") === false) {
        that.addClass("ascending");
      } else {
        that.removeClass("ascending");
      }

      /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
      * Sorting. Take first element and compare to every other one, if meets   *
      * current order conditions - swap places and for remaining of iterations *
      * new value will be compared. Then take second value and compare to      *
      * further values ('iter' is increased to avoid comparison with value(-s) *
      * before). Also, it knows in what order column was sorted by checking    *
      * its class and orders other way around.                                 *
      * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
      for (let i = 0; i < table.length - 1; i++) {
        for (let j = iter; j < table.length; j++) {
          let childMain = table[i].children[column].innerText;
          let childNext = table[j].children[column].innerText;
          if (childNext.toLowerCase() > childMain.toLowerCase() && that.hasClass("ascending") === true) {
            oldValue = table[i].innerHTML;
            table[i].innerHTML = table[j].innerHTML;
            table[j].innerHTML = oldValue;
          } else if (childNext.toLowerCase() < childMain.toLowerCase() && that.hasClass("ascending") === false) {
              oldValue = table[i].innerHTML;
              table[i].innerHTML = table[j].innerHTML;
              table[j].innerHTML = oldValue;
          }
        }
        iter += 1;
      }
    });
};
