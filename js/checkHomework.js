window.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('button[name="toSendHomework"]');

    button.addEventListener('click', () => {
        collectAllFormData();
    });
});

function collectAllFormData() {
    const forms = document.querySelectorAll('form.login__form');
    const formDatas = new FormData();

    forms.forEach((form, index) => {
        const formData = new FormData(form);
        const mergedValues = {};

        for (const [name, value] of formData.entries()) {
            if (name.endsWith('[]')) {
                const fieldName = `task${index}[${name.slice(0, -2)}][]`;
                mergedValues[fieldName] = (mergedValues[fieldName] || []).concat(value);
            } else {
                formDatas.append(`task${index}[${name}]`, value);
            }
        }

        for (const [name, values] of Object.entries(mergedValues)) {
            const mergedValue = values.join(' ');
            formDatas.append(name, mergedValue);
        }
    })

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/common/checkHomework.php'.concat(window.location.search), true);
    xhr.send(formDatas);
}
