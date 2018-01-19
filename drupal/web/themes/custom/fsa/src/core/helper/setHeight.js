function setHeight(element) {
  // Measure all content elements and assign their height to a css variable in the style attribute of the html.
  let childrenCombinedHeight = 0;
  console.log(element);
  [...element.children].forEach((child) => {
    // console.log(child);
    childrenCombinedHeight = childrenCombinedHeight + child.offsetHeight;
    // console.log(childrenCombinedHeight);
  });

  element.style.setProperty('--expanded' , `${childrenCombinedHeight}px`);
}

module.exports = setHeight;