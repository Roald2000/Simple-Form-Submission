
const request = async (route, params) => {
    const base_url = 'http://localhost:3302/api'; // dont hardcode base route for your api unless you are in development hehe
    return await fetch(`${base_url}${route}`, {
        body: JSON.stringify(params),
        method: 'POST'
    })
        .then(response => response.json())
        .catch(error => console.error(error));
}

document.querySelector('#submitForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = {
        email: document.querySelector('#email').value,
        first_name: document.querySelector('#first_name').value,
        last_name: document.querySelector('#last_name').value
    }

    let response = await request('/submit_post', payload);

    console.log(await response);

})