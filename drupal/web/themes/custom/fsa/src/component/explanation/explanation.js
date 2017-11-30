function explanation() {
  const explanationElementArray = [...document.querySelectorAll('.js-explanation')];
  explanationElementArray.forEach((element) => {
    element.setAttribute('data-header', 'FSA explains');
  });
}

module.exports = explanation;
