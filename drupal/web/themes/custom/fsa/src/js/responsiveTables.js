import checkMediaQuery from './checkMediaQuery';

function responsiveTables(elements, breakpoints) {
  var currentMediaQuery = checkMediaQuery();

  if (currentMediaQuery == breakpoints.small) {
    for (let i = 0; i < elements.length; i++) {
      elements[i].removeAttribute('role');
    }
  }


  const elemChildren = elements.map(elements => elements.children);

  const headerTexts = elemChildren.map(function(row) {
    const currentRow = [...row];
    for (let i = 0; i < currentRow.length; i++) {
      return currentRow[i].classList.contains('js-table-header') ? [...currentRow[i].children].map(function(text) {
        return text.innerHTML;
      }) : "";
    }
  });

  for (let i = 0; i < elemChildren.length; i++) {
    for (let y = 0; y < elemChildren[i].length; y++) {
      if (currentMediaQuery == breakpoints.small) {
        if (elemChildren[i][y].hasAttribute('role')) {
          elemChildren[i][y].removeAttribute('role');
        }
      }
      if (!elemChildren[i][y].classList.contains('js-table-header')) {
        const currentChildren = [...elemChildren[i][y].children];
        for (var x = 0; x < currentChildren.length; x++) {
          //currentChildren[x].dataset.header = headerTexts[i][x];
          currentChildren[x].setAttribute('data-header', headerTexts[i][x]);
        }
      }
    }
  }
}

module.exports = responsiveTables;
