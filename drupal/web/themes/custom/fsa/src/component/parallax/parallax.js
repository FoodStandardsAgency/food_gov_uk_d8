import InViewElement from '../../core/helper/inView';
import debounce from '../../core/helper/debounce';

function peek() {
  const peekElements = [...document.querySelectorAll('.js-parallax')];
  let peekElementInstanceArray = [];

  if (peekElements != null) {
    peekElements.forEach((element) => {
      peekElementInstanceArray =
        [...peekElementInstanceArray, new InViewElement(element)];
    });
  }

  let latestKnownScrollY = 0;
  let ticking = false;
  let currentValue;

  function calcCurrentValue(start, end, unit, itemOffset) {
    return `${start + (end - start) * ((window.innerHeight - itemOffset) / window.innerHeight)}${unit}`;
  }

  function update() {
    // reset the tick so we can
    // capture the next onScroll
    ticking = false;

    peekElementInstanceArray.forEach((item) => {
      let transforms = '';

      if (item.visible) {
        // console.log(item.dataset);
        item.dataset.forEach((object) => {
          currentValue = calcCurrentValue(object.start, object.end, object.unit, item.offset);
          if (object.element === undefined) {
            item.thisElement.style[object.declaration] = `${currentValue}`; // eslint-disable-line no-param-reassign
          } else {
            transforms = `${transforms} ${object.element}(${currentValue})`;
            item.thisElement.style.transform = transforms; // eslint-disable-line no-param-reassign  
          }
        });
      }
    });
  }

  function requestTick() {
    if (!ticking) {
      requestAnimationFrame(update);
    }
    ticking = true;
  }

  function onScroll() {
    latestKnownScrollY = window.scrollY;
    requestTick();
  }

  window.addEventListener('scroll', onScroll, false);

  // const handleScroll = debounce((e) => {
  //   console.log('Window scrolled. debounce')
  // }, 100);
  // window.addEventListener('scroll', handleScroll);
}

module.exports = peek;
