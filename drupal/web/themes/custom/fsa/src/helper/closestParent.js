const closestParent = function (child, match) {
  if (!child || child === document) {
    return null
  }

  if (child.classList.contains(match) || child.nodeName.toLowerCase() === match) {
    return child
  } else {
    return closestParent(child.parentNode, match)
  }
}

module.exports = closestParent
