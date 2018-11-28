function isColor (string) {
  return /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(string)
}

module.exports = isColor
