const baseDomain = document.baseURI;
const submitURL = 'partialuserform';
const buttons = () => Array.from(document.body.querySelectorAll('form.userform ul li.step-button-wrapper button'));
const formElements = () => Array.from(document.body.querySelectorAll('form.userform input, form.userform select, form.userform textarea'));

const submitPartial = () => {
  const data = new FormData();
  formElements().forEach((element) => {
    data.append(element.getAttribute('name'), element.getAttribute('value'));
  });
  const httpRequest = new XMLHttpRequest();
  httpRequest.open('POST', `${baseDomain}${submitURL}`, true);
  httpRequest.send(data);
};

const attachSubmitPartial = (button) => {
  console.log(button);
  button.addEventListener('click', submitPartial);
};

export default function () {
  buttons().forEach(attachSubmitPartial);
}
