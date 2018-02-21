function setHeight (element) {
  // Measure all content elements and assign their height to a css variable in the style attribute of the html.
  let childrenCombinedHeight = 0;
  [...element.children].forEach((child) => {
    childrenCombinedHeight = childrenCombinedHeight + child.offsetHeight
  })

  element.style.setProperty('--expanded', `${childrenCombinedHeight}px`)
}

module.exports = setHeight
