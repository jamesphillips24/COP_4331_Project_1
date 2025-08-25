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

        fetch("/LAMPAPI", {
            method: "GET",
            headers: { "Content-Type": "application/json"},
            body: JSON.stringify(signupData)
        })
        .then(response => response.json())
        .then(data => {
            
        })
        .catch(err => console.error("Error:", err));
    });
}



//sends login info to backend and handles response by saving cookie data and sending to contact page
if (loginBtn){
    loginBtn.addEventListener("click", function(){

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;


        const loginData = {
            username: username,
            password: password
        };

        console.log(loginData);

        fetch("http://localhost/LAMPAPI/Login.php/", {
        method: "POST",
        headers: { "Content-Type": "application/json"},
        body: JSON.stringify(loginData)
        })
        .then(response => response.json())
        .then(data => {
            if(data.id <0 ) console.error("Login failed:", data.error);// failure case 
            else if (data.id > 0 ){
                saveUser(data);
                window.location.href = "contacts.html";
            }
        })
        .catch(err => console.error("Error:", err));
    });
}


//takes in object that represents a user, returned by login response from backend.
//is used to create or refresh cookies to represent that this is the logged in user.
//called on successful login, creating cookie, called after all CRUD operations to refresh the timer. lasts 20 minutes.
function saveUser(data){
    const user = {
        id: data.id,
        firstName: data.firstName,
        lastName: data.lastName
    };

    const userString = JSON.stringify(user);
    const encoded = encodeURIComponent(userString);
    const expires = new Date(Date.now() + 20*60*1000).toUTCString();
    document.cookie = `user=${encoded}; expires = ${expires}; path =/`;
}

//checks to see current cookie data. if not found, send to login screen to relog. if found, returns ID #
function readUser(){
    const c = document.cookie.split("; ").find(c => c.startsWith("user="));
    if(!c) {
        window.location.href = "login.html";
        return null;
    }
    else try{
        return JSON.parse(decodeURIComponent(c.split("="[1])));
    } catch { return null;}
}