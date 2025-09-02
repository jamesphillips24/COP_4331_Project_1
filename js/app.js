const signupBtn = document.getElementById("signupBtn");
const loginBtn = document.getElementById("loginBtn");

document.addEventListener("DOMContentLoaded", () => {
    if (loginBtn){
        logIn();
    }
    if(signupBtn){
        signUp();
    }
    /*if(contactsTableBody){
        contacts();
    }*/
})


//sends login info to backend and handles response by saving cookie data and sending to contact page
function logIn(){
    loginBtn.addEventListener("click", function(){6

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;


        const loginData = {
            username: username,
            password: password
        };

        console.log(loginData);

        fetch("/LAMPAPI/Login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json"},
        body: JSON.stringify(loginData)
        })
        
        /*  debug   
        .then(response => response.text())
        .then(data => {
        console.log("Response:", data);
        })
                  */
        
        .then(response => response.json())
        .then(data => {
            if(data.id <0 ){
                console.error("Login failed:", data.error);// failure case 
                document.getElementById("error").style.display = "block";
            }
            else if (data.id > 0 ){
                saveUser(data);
                window.location.href = "contacts.html";
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function signUp(){
    signupBtn.addEventListener("click", function(){
        const name = document.getElementById("name").value;
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const confirmPass = document.getElementById("confirmPassword").value;

        const signupData = {
            name: name,
            username: username,
            password: password,
            confirmPass: confirmPass
        };

        console.log(signupData);
console.log("test1");

        fetch("/LAMPAPI/SignUp.php", {
            method: "POST",
            headers: { "Content-Type": "application/json"},
            body: JSON.stringify(signupData)
        })
/*debug*//*
        .then(response => response.text())
        .then(data => {
        console.log("Response:", data);
        })
*/
        .then(response => response.json())
        .then(data => {
            // -1 means pw doesnt match
            // -2 means username already used
            if(data.id == -1){
                console.error("Passwords do not match:", data.error);// failure case 
                document.getElementById("error1").style.display = "block";
                console.log("pw dont match");
            }
            if(data.id == -2){
                console.error("Login failed:", data.error);// failure case 
                document.getElementById("error2").style.display = "block";
                console.log("username exists alr")
            }
console.log("test2");
        })
        .catch(err => console.error("Error:", err));
    });
}

function contacts(){
    function fetchContacts(search){
        let user = readUser();
        fetch("/LAMPAPI/Contacts.php", {
            method: "POST",
            headers: { "Content-Type": "application/json"},
            body: JSON.stringify({userId: user, search: search})
        })
        .then(response => response.json())
        .then(data => {
            // procedure for fetching contacts 
        })
        .catch(err => console.error("Error:", err));
    }

    const q = document.getElementById("searchInput");


    fetchContacts("");



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
    document.cookie = `user=${encoded}; expires=${expires}; path=/`;
}

//checks to see current cookie data. if not found, send to login screen to relog. if found, returns ID #
function readUser(){
    const c = document.cookie.split("; ").find(c => c.startsWith("user="));
    if(!c) {
        window.location.href = "login.html";
        return null;
    }
    else try{
        return JSON.parse(decodeURIComponent(c.split("=")[1]));
    } catch { return null;}
}