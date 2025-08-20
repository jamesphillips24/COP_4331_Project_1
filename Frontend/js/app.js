const signupBtn = document.getElementById("signupBtn");
const loginBtn = document.getElementById("loginBtn");

if(signupBtn){
    signupBtn.addEventListener("click", function(){
        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const confirmPass = document.getElementById("confirmPassword").value;

        const signupData = {
            name: name,
            email: email,
            username: username,
            password: password,
            confirmPass: confirmPass
        };

        console.log(signupData);

        fetch("/api/signup", {
            method: "GET",
            headers: { "Content-Type": "application/json"},
            body: JSON.stringify(signupData)
        })
        .then(response => {

        })
        .then(data => {

        })
        .catch(err => {

        });
        });
}

if (loginBtn){
    loginBtn.addEventListener("click", function(){

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;


        const loginData = {
            username: username,
            password: password
        };

        console.log(loginData);

        fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/json"},
        body: JSON.stringify(loginData)
        })
        .then(response => {

        })
        .then(data => {

        })
        .catch(err => {

        });
    });
}