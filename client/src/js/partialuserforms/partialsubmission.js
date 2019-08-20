const baseDomain = document.baseURI;
const submitURL = 'partialuserform';
const buttons = () => Array.from(document.body.querySelectorAll('form.userform ul li.step-button-wrapper button'));
const formElements = () => Array.from(document.body.querySelectorAll('form.userform [name]:not([type=hidden]):not([type=submit])'));

const getElementValue = (element, fieldName) => {
  const value = element.value;
  if (element.getAttribute('type') === 'select') {
    return element[element.selectedIndex].value;
  } else if (element.getAttribute('type') === 'radio') {
    const name = `[name=${fieldName}]:checked`;
    const checkedElement = document.body.querySelector(name);
    return checkedElement !== null ? checkedElement.value : '';
  } else if (element.getAttribute('type') === 'checkbox') {
    const name = `[name="${fieldName}"]:checked`;
    const checkedElements = Array.from(document.body.querySelectorAll(name));
    const valueArray = [];
    if (checkedElements.length > 0) {
      checkedElements.forEach((element) => {
        valueArray.push(element.value);
      });
      return valueArray;
    }
    return '';
  } else {
    return value;
  }
};

const submitPartial = () => {
  const data = new FormData();
  formElements().forEach((element) => {
    const fieldName = element.getAttribute('name');
    const value = getElementValue(element, fieldName);
    if (!data.has(fieldName)) {
      if (typeof value === 'object') {
        value.forEach((arrayValue) => {
          data.append(fieldName, arrayValue);
        })
      } else {
        data.append(fieldName, value);
      }
    }
  });
  /** global: XMLHttpRequest */
  const httpRequest = new XMLHttpRequest();
  httpRequest.open('POST', `${baseDomain}${submitURL}`, true);
  httpRequest.send(data);
};

const attachSubmitPartial = (button) => {
  button.addEventListener('click', submitPartial);
};

export default function () {
  buttons().forEach(attachSubmitPartial);
}
