class InViewElement {
  constructor(element) {
    this.element = element;
  }

  get thisElement() {
    return this.element;
  }

  get visible() {
    const rect = this.element.getBoundingClientRect();
    return (
      rect.top - window.innerHeight <= 0 &&
      rect.bottom >= 0
    );
  }

  get offset() {
    return this.calcOffset();
  }

  calcOffset() {
    return this.element.getBoundingClientRect().top;
  }

  get dataset() {
    const transforms = ['scale', 'translateY', 'rotate'];
    const datasetArray = this.element.dataset;
    let datasetObjectArray = [];

    Object.keys(datasetArray).forEach((key) => {
      const valueUnitArray = datasetArray[key].split(',');

      const valueUnitExpr = /(\d*\.?\d*)(.*)/;
      const valueArray = valueUnitArray.map((valueUnit) => {
        return parseInt(valueUnit.match(valueUnitExpr)[1], 10);
      });
      const unit = valueUnitArray[0].match(valueUnitExpr)[2];

      datasetObjectArray = [...datasetObjectArray, {
        declaration: key,
        element: transforms.indexOf(key) >= 0 ? key : undefined,
        start: valueArray[0],
        end: valueArray[1],
        unit,
      }];
    });

    return datasetObjectArray;
  }

  set addClass(newCssClass) {
    this.element.classList.add(newCssClass);
  }
}

module.exports = InViewElement;
