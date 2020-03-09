function webFormError(){
  const errorBox = document.querySelector('.error-summary');
  if (errorBox) {
    errorBox.focus();
    document.title = "Error: " + document.title;
  }
}

module.exports = webFormError
