const baseDomain = document.baseURI;
const submitURL = 'partialuserform';
const buttons = () => Array.from(document.body.querySelectorAll('form.userform ul li.step-button-wrapper button'));
const formElements = () => Array.from(document.body.querySelectorAll('form.userform [name]'));

const getElementValue = (element, fieldName) => {
  let value = element.value;
  if (element.getAttribute('type') === 'select') {
    value = element[element.selectedIndex].value;
  }
  if (element.getAttribute('type') === 'radio') {
    const name = `[name=${fieldName}]:checked`;
    const checkedElement = document.body.querySelector(name);
    if (checkedElement !== null) {
      value = checkedElement.value;
    }
  }
  if (element.getAttribute('type') === 'checkbox') {
    const name = `[name="${fieldName}"]:checked`;
    const checkedElements = Array.from(document.body.querySelectorAll(name));
    const valueArray = [];
    if (checkedElements.length > 0) {
      checkedElements.forEach((element) => {
        valueArray.push(element.value);
      });
      value = valueArray;
    }
  }
  return value;
};

const submitPartial = () => {
  const data = new FormData();
  formElements().forEach((element) => {
    const fieldName = element.getAttribute('name');
    console.log(data);
    data.append(fieldName, getElementValue(element, fieldName));
  });
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
