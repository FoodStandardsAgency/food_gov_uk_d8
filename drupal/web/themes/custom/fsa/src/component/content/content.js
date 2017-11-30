import guid from '../../core/helper/guid';

function addHeading() {
  const regionalVariationElementArray = [...document.querySelectorAll('.js-regional-variation')];
  const explanationElementArray = [...document.querySelectorAll('.js-explanation')];
  const elementArray = [...regionalVariationElementArray, ...explanationElementArray];

  elementArray.forEach((element) => {
    const id = guid();
    const heading = document.createElement('h3');
    const paragraph = document.createElement('p');
    paragraph.innerHTML = element.innerHTML;
    heading.classList.add(`heading`);
    heading.id = id;
    if (element.classList.contains('js-england')) {
      heading.innerHTML = Drupal.t(`Difference in England`);
    } else if (element.classList.contains('js-wales')) {
      heading.innerHTML = Drupal.t(`Difference in Wales`);
    } else if (element.classList.contains('js-northern-ireland')) {
      heading.innerHTML = Drupal.t(`Difference in Northern Ireland`);
    } else if (element.classList.contains('js-explanation')) {
      heading.innerHTML = Drupal.t(`FSA Explains`);
    }

    element.innerHTML = ``;
    element.appendChild(heading);
    element.appendChild(paragraph);
    element.setAttribute(`aria-labelledby`, id);
  });
}

function printPage() {
  const printPDFWrapperElements = [...document.querySelectorAll('.print__wrapper--pdf')];
  if (printPDFWrapperElements.length > 0) {
    for (let i = 0; i < printPDFWrapperElements.length; i++) {
      // Create the wrapper
      let printWrapper = document.createElement("div");
      printWrapper.classList.add("print-wrapper");
  
      // Add it to dom
      printPDFWrapperElements[i].parentNode.insertBefore(printWrapper, printPDFWrapperElements[i]);
  
      // Create the button
      let printButton = document.createElement("button");
      printButton.classList.add("print-page");
      printButton.innerHTML = Drupal.t('Print this page');
      printButton.addEventListener("click", function(e) {
        e.preventDefault();
        window.print();
      });
  
      // Move both print and view pdf button inside of the wrapper
      printWrapper.appendChild(printButton);
      printWrapper.appendChild(printPDFWrapperElements[i]);
    }
  }
}

module.exports = { addHeading, printPage };
