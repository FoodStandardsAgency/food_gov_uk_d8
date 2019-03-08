/* global Drupal */
import guid from '../../helper/guid';

function addHeading () {
  const regionalVariationElementArray = [...document.querySelectorAll('.js-regional-variation')];
  const explanationElementArray = [...document.querySelectorAll('.js-explanation')];
  const elementArray = [...regionalVariationElementArray, ...explanationElementArray];
  const calloutStylesArray = [...document.querySelectorAll('.js-callout-style')];
  const hasDrupal = typeof Drupal !== 'undefined';

  elementArray.forEach((element) => {
    const id = guid();
    const heading = document.createElement('h3');
    const paragraph = document.createElement('div');

    paragraph.innerHTML = element.innerHTML;
    paragraph.classList.add(`regional-variation__content`);
    heading.classList.add(`heading`);
    heading.classList.add(`regional-variation__heading`);
    heading.id = id;
    if (element.classList.contains('js-england')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`England`) : 'England';
    } else if (element.classList.contains('js-england-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`England and wales`) : 'England and Wales';
    } else if (element.classList.contains('js-england-northern-ireland')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`England and Northern Ireland`) : 'England and Northern Ireland';
    } else if (element.classList.contains('js-northern-ireland-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t('Northern Ireland and wales') : 'Northern Ireland and wales';
    } else if (element.classList.contains('js-wales')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`Wales`) : 'Wales';
    } else if (element.classList.contains('js-northern-ireland')) {
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`Northern Ireland`) : 'Northern Ireland';
    } else if (element.classList.contains('js-explanation')) {
      element.setAttribute('role', 'complementary');
      heading.classList.add(`heading--small`);
      heading.innerHTML = hasDrupal ? Drupal.t(`FSA Explains`) : 'FSA Explains';
      heading.classList.remove(`regional-variation__heading`);
      heading.classList.add(`explanation__title`);
      paragraph.classList.add(`important`);
      paragraph.classList.remove(`regional-variation__content`);
      paragraph.classList.add(`explanation__content`);
    }

    element.innerHTML = ``;
    element.appendChild(heading);
    element.appendChild(paragraph);
    element.setAttribute(`aria-labelledby`, id);
  });

  calloutStylesArray.forEach((element) => {
    const id = guid();
    const heading = document.createElement('h3');
    const paragraph = document.createElement('div');
    paragraph.innerHTML = element.innerHTML;
    heading.id = id;

    element.setAttribute('role', 'complementary');
    heading.classList.add(`heading`);
    heading.classList.add(`heading--small`);
    heading.classList.add(`explanation__title`);
    paragraph.classList.add(`important`);
    paragraph.classList.add(`explanation__content`);

    if (element.classList.contains('explanation-style')) {
      heading.innerHTML = hasDrupal ? Drupal.t(`FSA Explains`) : 'FSA Explains';
    }
    else if (element.classList.contains('best-practice-style')) {
      heading.innerHTML = hasDrupal ? Drupal.t(`Best practice`) : 'Best practice';
      heading.classList.add(`explanation__title--green`);
    }
    else if (element.classList.contains('tips-style')) {
      heading.innerHTML = hasDrupal ? Drupal.t(`Tips`) : 'Tips';
      heading.classList.add(`explanation__title--purple`);
    }
    else if (element.classList.contains('legal-advice-style')) {
      heading.innerHTML = hasDrupal ? Drupal.t(`Legal advice`) : 'Legal advice';
      heading.classList.add(`explanation__title--blue`);
    }
    else if (element.classList.contains('important-style')) {
      heading.innerHTML = hasDrupal ? Drupal.t(`Important`) : 'Important';
      heading.classList.add(`explanation__title--red`);
    }


    element.innerHTML = ``;
    element.appendChild(heading);
    element.appendChild(paragraph);
    element.setAttribute(`aria-labelledby`, id);
  });

}

module.exports = addHeading;
