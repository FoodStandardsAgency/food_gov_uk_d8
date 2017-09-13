function printPage(printPDFWrapperElements) {
  for (let i = 0; i < printPDFWrapperElements.length; i++) {
    // Create a button
    let printButton = document.createElement("button");
    printButton.classList.add("print-page");
    printButton.innerHTML = `Print this page`;
    printButton.addEventListener("click", function(e) {
      e.preventDefault();
      window.print();
    });
    printPDFWrapperElements[i].parentNode.insertBefore(printButton, printPDFWrapperElements[i].nextSibling);
  }
}

module.exports = printPage;
