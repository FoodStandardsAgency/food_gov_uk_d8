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
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`England specific guidance`);
    } else if (element.classList.contains('js-england-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`England and wales specific guidance`);
    } else if (element.classList.contains('js-england-northern-ireland')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`England and Northern Ireland specific guidance`);
    } else if (element.classList.contains('js-northern-ireland-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`Northern Ireland and wales specific guidance`);
    } else if (element.classList.contains('js-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`Wales specific guidance`);
    } else if (element.classList.contains('js-northern-ireland')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`Northern Ireland specific guidance`);
    } else if (element.classList.contains('js-explanation')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = Drupal.t(`FSA Explains`);
      heading.classList.add(`explanation__title`);
    }

    element.innerHTML = ``;
    element.appendChild(heading);
    element.appendChild(paragraph);
    element.setAttribute(`aria-labelledby`, id);
  });
}

// function printPage() {
//   const printPDFWrapperElements = [...document.querySelectorAll('.print__wrapper--pdf')];
//   if (printPDFWrapperElements.length > 0) {
//     for (let i = 0; i < printPDFWrapperElements.length; i++) {
//       // Create the wrapper
//       let printWrapper = document.createElement("div");
//       printWrapper.classList.add("print-wrapper");
  
//       // Add it to dom
//       printPDFWrapperElements[i].parentNode.insertBefore(printWrapper, printPDFWrapperElements[i]);
  
//       // Create the button
//       let printButton = document.createElement("button");
//       printButton.classList.add("print-page");
//       printButton.innerHTML = Drupal.t('Print this page');
//       printButton.addEventListener("click", function(e) {
//         e.preventDefault();
//         window.print();
//       });
  
//       // Move both print and view pdf button inside of the wrapper
//       printWrapper.appendChild(printButton);
//       printWrapper.appendChild(printPDFWrapperElements[i]);
//     }
//   }
// }

module.exports = addHeading;
