/* global Drupal */
import guid from '../../helper/guid';

function addHeading () {
  const elementArray = [...document.querySelectorAll('.js-regional-variation')];
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
    heading.setAttribute(`role`, 'heading');
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
    }

    element.innerHTML = ``;
    element.appendChild(heading);
    element.appendChild(paragraph);
    element.setAttribute(`aria-labelledby`, id);
  });

}

module.exports = addHeading;
