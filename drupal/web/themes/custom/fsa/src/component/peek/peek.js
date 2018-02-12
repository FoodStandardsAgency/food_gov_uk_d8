import InViewElement from '../../helper/inView';
import debounce from '../../helper/debounce';

function peek() {
  const peekElements = [...document.querySelectorAll('.js-peek')];

  let peekElementInstanceArray = [];

  if (peekElements != null) {
    peekElements.forEach((element) => {
      element.classList.add('peek');
      peekElementInstanceArray =
        [...peekElementInstanceArray, new InViewElement(element)];
    });
  }

  // Set default dataset
  peekElementInstanceArray.forEach((item) => {
    // if (item.dataset.length === 0) {
    //   item.dataset = {
    //     declaration: 'transform',
    //     element: 'translateY',
    //     start: 0,
    //     end: -10,
    //     unit: 'em',
    //   };
    // }
  });

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

  update();
  window.addEventListener('scroll', onScroll, false);

  // const handleScroll = debounce((e) => {
  //   console.log('Window scrolled. debounce')
  // }, 100);
  // window.addEventListener('scroll', handleScroll);
}

module.exports = peek;
