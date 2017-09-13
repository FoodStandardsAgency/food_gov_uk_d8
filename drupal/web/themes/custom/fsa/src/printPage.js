function printPage(printPDFWrapperElements) {
  for (let i = 0; i < printPDFWrapperElements.length; i++) {
    // Create the wrapper
    let printWrapper = document.createElement("div");
    printWrapper.classList.add("print-wrapper");

    // Add it to dom
    printPDFWrapperElements[i].parentNode.insertBefore(printWrapper, printPDFWrapperElements[i]);

    // Create the button
    let printButton = document.createElement("button");
    printButton.classList.add("print-page");
    printButton.innerHTML = `Print this page`;
    printButton.addEventListener("click", function(e) {
      e.preventDefault();
      window.print();
    });

    // Move both print and view pdf button inside of the wrapper
    printWrapper.appendChild(printButton);
    printWrapper.appendChild(printPDFWrapperElements[i]);
  }
}

module.exports = printPage;
