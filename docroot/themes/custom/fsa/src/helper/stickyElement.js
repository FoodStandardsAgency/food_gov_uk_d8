function stickyElement (containers, stickyElements) {
  // Area class
  class Area {
    constructor (element) {
      this.element = element
    }

    set relatedInstance (item) {
      this._relatedInstance = item
    }

    get relatedInstance () {
      return this._relatedInstance
    }

    get thisElement () {
      return this.element
    }

    get offset () {
      return this.calcOffset()
    }

    calcOffset () {
      return this.element.getBoundingClientRect().top
    }

    get inview () {
      return this.calcInview()
    }

    calcInview () {
      var rect = this.element.getBoundingClientRect()
      return (
        rect.top - window.innerHeight <= 0 &&
        rect.bottom >= 0
      )
    }

    get isBottom () {
      return this.calcBottom()
    }

    calcBottom () {
      var elementHeight = this._relatedInstance.element.offsetHeight
      return this.element.getBoundingClientRect().bottom <= elementHeight
    }
  }

  // Content section class
  class Section extends Area {

  }

  const containerArray = []
  const stickyElementArray = []

  // Push all containerArray into an array
  for (let i = 0; i < containers.length; i++) {
    // Query all content sections inside area
    let allSections = stickyElements

    // Loop through every sections inside current content area
    for (let y = 0; y < allSections.length; y++) {
      let stickyScrollableElement = allSections[y]
      stickyElementArray.push(new Section(stickyScrollableElement))
    }

    containerArray.push(new Area(containers[i]))
  }

  // Set related instance for each instance of the container
  for (var i = 0; i < containerArray.length; i++) {
    containerArray[i].relatedInstance = stickyElementArray[i]
  }

  // Function to toggle sticky navigation
  const toggleStickyElement = () => {
    // Get header element to check if in viewport.
    const headerElement = document.getElementsByClassName('hero-wrapper')
    const header  = new Area(headerElement[0])

    containerArray.forEach(function (container) {

      // Check if element is bottom of the content area  and header is not in
      // view.
      if (container.isBottom  && !header.inview) {
        container.relatedInstance.element.classList.add('is-bottom')
      } else {
        container.relatedInstance.element.classList.remove('is-bottom')
      }

      // Check if element is in view.
      if (container.inview && container.offset < 0) {
        container.relatedInstance.element.classList.add('is-sticky')
      } else {
        container.relatedInstance.element.classList.remove('is-sticky')
      }
    })
  }

  // Add scroll listener
  window.addEventListener('scroll', toggleStickyElement)

  // Add load listener
  window.addEventListener('load', toggleStickyElement)
}

module.exports = stickyElement
