import guid from '../../helper/guid'

function addHeading () {
  const regionalVariationElementArray = [...document.querySelectorAll('.js-regional-variation')]
  const explanationElementArray = [...document.querySelectorAll('.js-explanation')]
  const elementArray = [...regionalVariationElementArray, ...explanationElementArray]

  elementArray.forEach((element) => {
    const id = guid()
    const heading = document.createElement('h3')
    const paragraph = document.createElement('div')
    paragraph.innerHTML = element.innerHTML
    heading.classList.add(`heading`)
    heading.id = id
    if (element.classList.contains('js-england')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`England specific guidance`)
    } else if (element.classList.contains('js-england-wales')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`England and wales specific guidance`)
    } else if (element.classList.contains('js-england-northern-ireland')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`England and Northern Ireland specific guidance`)
    } else if (element.classList.contains('js-northern-ireland-wales')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`Northern Ireland and wales specific guidance`)
    } else if (element.classList.contains('js-wales')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`Wales specific guidance`)
    } else if (element.classList.contains('js-northern-ireland')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`Northern Ireland specific guidance`)
    } else if (element.classList.contains('js-explanation')) {
      heading.classList.add(`heading--small`)
      heading.innerHTML = Drupal.t(`FSA Explains`)
      heading.classList.add(`explanation__title`)
      paragraph.classList.add(`important`)
    }

    element.innerHTML = ``
    element.appendChild(heading)
    element.appendChild(paragraph)
    element.setAttribute(`aria-labelledby`, id)
  })
}

module.exports = addHeading
