function regionalVariation() {
  // Regional variations
  const regionalVariationElements = [...document.querySelectorAll('.js-regional-variation')];
  if (regionalVariationElements.length > 0) {
    for (let i = 0; i < regionalVariationElements.length; i++) {
      if (regionalVariationElements[i].classList.contains('js-england')) {
        //regionalVariationElements[i].dataset.header = 'Difference in England';
        regionalVariationElements[i].setAttribute('data-header', 'Difference in England');
      } else if (regionalVariationElements[i].classList.contains('js-wales')) {
        //regionalVariationElements[i].dataset.header = 'Difference in Wales';
        regionalVariationElements[i].setAttribute('data-header', 'Difference in Wales');
      } else if (regionalVariationElements[i].classList.contains('js-northern-ireland')) {
        //regionalVariationElements[i].dataset.header = 'Difference in Northern Ireland';
        regionalVariationElements[i].setAttribute('data-header', 'Difference in Northern Ireland');
      }
    }
  }
}

module.exports = regionalVariation;
