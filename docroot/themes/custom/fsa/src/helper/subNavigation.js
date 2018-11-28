function subNavigation (element) {
  const toggleContentVisibility = (button, content) => {
    if (content.classList.contains('is-open')) {
      content.classList.remove('is-open')
      content.classList.add('is-closed')
      content.setAttribute('aria-hidden', true)
      button.classList.remove('is-open')
      button.classList.add('is-closed')
      button.setAttribute('aria-expanded', false)
    } else {
      content.classList.add('is-open')
      content.classList.remove('is-closed')
      content.setAttribute('aria-hidden', false)
      button.classList.add('is-open')
      button.classList.remove('is-closed')
      button.setAttribute('aria-expanded', true)
    }
  }

  const closeAll = (array) => {
    array.forEach(function (link) {
      let content = link.nextElementSibling
      content.classList.remove('is-open')
      content.classList.add('is-closed')
      content.setAttribute('aria-hidden', true)
      link.classList.remove('is-open')
      link.classList.add('is-closed')
      link.setAttribute('aria-expanded', false)
    })
  }

  const linkArray = [...element.querySelectorAll('a')]
  let contentLinks = []
  linkArray.forEach(function (link) {
    if (link.nextElementSibling !== null) {
      contentLinks = [...contentLinks, link]
      let content = link.nextElementSibling
      link.addEventListener('click', function (e) {
        e.preventDefault()
        closeAll(contentLinks)
        toggleContentVisibility(link, content)
      })
    }
  })
  console.log(linkArray)
}

module.exports = subNavigation
