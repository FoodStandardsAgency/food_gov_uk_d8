function regionalVariation(elements) {

  for (let i = 0; i < elements.length; i++) {
    if (elements[i].classList.contains('js-england')) {
      //elements[i].dataset.header = 'Difference in England';
      elements[i].setAttribute('data-header', 'Difference in England');
    } else if (elements[i].classList.contains('js-wales')) {
      //elements[i].dataset.header = 'Difference in Wales';
      elements[i].setAttribute('data-header', 'Difference in Wales');
    } else if (elements[i].classList.contains('js-northern-ireland')) {
      //elements[i].dataset.header = 'Difference in Northern Ireland';
      elements[i].setAttribute('data-header', 'Difference in Northern Ireland');
    }
  }
}

module.exports = regionalVariation;
