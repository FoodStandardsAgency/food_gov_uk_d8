import hasClass from './hasClass';

function nextByClass(node, cls) {
  while (node = node.nextSibling) {
      if (hasClass(node, cls)) {
          return node;
      }
  }
  return null;
}

module.exports = nextByClass;
