import checkMediaQuery from './checkMediaQuery'
import breakpoints from './breakpoints'

function responsiveTables () {
  // Query elements
  const tableElementsArray = [...document.querySelectorAll('.js-table')]

  if (tableElementsArray <= 0) {
    return false
  }

  if (checkMediaQuery() === breakpoints.small || checkMediaQuery() === breakpoints.xsmall) {
    for (let i = 0; i < tableElementsArray.length; i++) {
      tableElementsArray[i].removeAttribute('role')
    }
  }

  const elemChildren = tableElementsArray.map(tableElementsArray => tableElementsArray.children)

  const headerTexts = elemChildren.map(function (row) {
    const currentRow = [...row]
    for (let i = 0; i < currentRow.length; i++) {
      return currentRow[i].classList.contains('js-table-header') ? [...currentRow[i].children].map(function (text) {
        return text.innerHTML
      }) : ''
    }
  })

  for (let i = 0; i < elemChildren.length; i++) {
    for (let y = 0; y < elemChildren[i].length; y++) {
      if (checkMediaQuery() === breakpoints.small || checkMediaQuery() === breakpoints.xsmall) {
        if (elemChildren[i][y].hasAttribute('role')) {
          elemChildren[i][y].removeAttribute('role')
        }
      }
      if (!elemChildren[i][y].classList.contains('js-table-header')) {
        const currentChildren = [...elemChildren[i][y].children]
        for (var x = 0; x < currentChildren.length; x++) {
          // currentChildren[x].dataset.header = headerTexts[i][x];
          currentChildren[x].setAttribute('data-header', headerTexts[i][x])
        }
      }
    }
  }
}

export default responsiveTables
