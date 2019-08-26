const baseDomain = document.baseURI;
const submitURL = 'partialuserform/save';
const buttons = () => Array.from(document.body.querySelectorAll('form.userform ul li.step-button-wrapper button'));
const formElements = () => Array.from(document.body.querySelectorAll('form.userform [name]:not([type=hidden]):not([type=submit])'));
const requests = [];

const getElementValue = (element, fieldName) => {
  const value = element.value;
  if (element.getAttribute('type') === 'select') {
    return element[element.selectedIndex].value;
  }
  if (element.getAttribute('type') === 'radio') {
    const name = `[name=${fieldName}]:checked`;
    const checkedElement = document.body.querySelector(name);
    return checkedElement !== null ? checkedElement.value : "";
  }
  if (element.getAttribute('type') === 'checkbox') {
    const name = `[name="${fieldName}"]:checked`;
    const checkedElements = Array.from(document.body.querySelectorAll(name));
    const valueArray = [];
    if (checkedElements.length > 0) {
      checkedElements.forEach((element) => {
        valueArray.push(element.value);
      });
      return valueArray;
    }
    return "";
  }
  return value;
};

const submitPartial = () => {
  const data = new FormData();
  formElements().forEach(element => {
    const fieldName = element.getAttribute('name');
    const value = getElementValue(element, fieldName);
    if (!data.has(fieldName)) {
      if (typeof value === 'object') {
        value.forEach(arrayValue => {
          data.append(fieldName, arrayValue);
        });
      } else {
        data.append(fieldName, value);
      }
    }
  });

  /** global: XMLHttpRequest */
  const httpRequest = new XMLHttpRequest();
  requests.push(httpRequest);
  httpRequest.open('POST', `${baseDomain}${submitURL}`, true);
  httpRequest.send(data);
};

const attachSubmitPartial = (button) => {
  button.addEventListener('click', submitPartial);
};

export default function () {
  buttons().forEach(attachSubmitPartial);

  // Clear all pending partial submissions on submit
  const form = document.body.querySelector('form.userform');
  if (form !== null) {
    form._submit = form.submit; // Save reference
    form.submit = () => {
      if (!confirm("Are you sure you want to submit this form?")) {
        return;
      }

      // Abort all requests
      if (requests.length) {
        requests.forEach(xhr => {
          xhr.abort();
        });
      }
      form._submit();
    };
  }
}
