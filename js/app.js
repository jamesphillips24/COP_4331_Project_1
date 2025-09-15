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
    loginBtn.addEventListener("click", function(){

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

        fetch("/LAMPAPI/SignUp.php", {
            method: "POST",
            headers: { "Content-Type": "application/json"},
            body: JSON.stringify(signupData)
        })

/* debug: show raw text response *//*
        .then(r => r.text())
.then(text => { console.log("RAW RESPONSE:\n", text); })
.catch(err => console.error("Fetch failed:", err));
*/

/*debug*//*
        .then(response => response.text())
        .then(data => {
        console.log("Response:", data);
        })
*/
        .then(response => response.json())
        .then(data => {
            //checks if username or passwords are valid
            if(data.id == -3){
                console.error("Please fill in missing boxes");
                document.getElementById("errorSignup").innerHTML = "Please fill in missing boxes";
                document.getElementById("errorSignup").style.visibility = "visible";
            }
            else if(data.id == -4){
                console.error("Username and password cannot contain spaces")
                document.getElementById("errorSignup").innerHTML =  '<span style="line-height:3">Username and password</span><br>' +
                                                                    '<span style="line-height:0">cannot contain spaces</span>';
                document.getElementById("errorSignup").style.visibility = "visible";
            }

            // -1 means pw doesnt match
            // -2 means username already used
            else if(data.id == -1){
                console.error("Passwords do not match:", data.error);// failure case
                document.getElementById("errorSignup").innerHTML = "Passwords do not match";
                document.getElementById("errorSignup").style.visibility = "visible";
            }
            else if(data.id == -2){
                console.error("Username already in use", data.error);// failure case
                document.getElementById("errorSignup").innerHTML = "Username already in use";
                document.getElementById("errorSignup").style.visibility = "visible";
            }

            //successful signup, automatically logs in and then redirects to contacts page
            else if (data.id > 0 ){
                saveUser(data);
                window.location.href = "contacts.html";
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function contacts() {
  const q = document.getElementById("searchInput");
  const contactsTableBody = document.getElementById("contactsTableBody");

  function fetchContacts(search){
      const user = readUser();
      if (!user) return;

      fetch("/LAMPAPI/Contacts.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
              mode: 6,
              searchterm: search || "",
              id: user.id
          })
      })
      .then(r => r.json())
      .then(data => {
          let results = data.searchResults || [];
          if (!Array.isArray(results)) results = Object.values(results);

          contactsTableBody.innerHTML = "";

          if (results.length === 0) {
              contactsTableBody.innerHTML = '<tr><td colspan="5">No contacts found</td></tr>';
              return;
          }

          results.forEach(contact => {
              console.log("Contact object:", contact);      //DEBUG
              const tr = document.createElement("tr");
              tr.innerHTML = `
                  <td>${contact.FirstName}</td>
                  <td>${contact.LastName}</td>
                  <td>${contact.Email}</td>
                  <td>${contact.Phone}</td>
                  <td class="col-actions">
                    <button class="edit-btn" data-id="${contact.ID}">Edit</button>
                    <button class="delete-btn" data-id="${contact.ID}">Delete</button>
                  </td>
              `;
              contactsTableBody.appendChild(tr);
          });

          contactsTableBody.querySelectorAll(".delete-btn").forEach(btn => {
              btn.addEventListener("click", () => deleteContact(btn.dataset.id));
          });
          contactsTableBody.querySelectorAll(".edit-btn").forEach(btn => {
              btn.addEventListener("click", () => editContact(btn.dataset.id));
          });
      })
      .catch(err => console.error("Error fetching contacts:", err));
  }

  fetchContacts("");

  if (q) {
      let timeout;
      q.addEventListener("input", () => {
          clearTimeout(timeout);
          timeout = setTimeout(() => fetchContacts(q.value.trim()), 250);
      });
  }
//adding
    document.getElementById("addContactBtn").addEventListener("click", () => {
        document.getElementById("addContactForm").style.display = "block";
    });

    document.getElementById("cancelContactBtn").addEventListener("click", () => {
        document.getElementById("addContactForm").style.display = "none";
    });
//editing
    document.getElementById("cancelEditContactBtn").addEventListener("click", () => {
        document.getElementById("editContactForm").style.display = "none";
    });


  document.getElementById("saveContactBtn").addEventListener("click", () => {
      const user = readUser();
      const newContact = {
          mode: 5,
          id: user.id,
          InputFirstName: document.getElementById("contactFirstName").value,
          InputLastName: document.getElementById("contactLastName").value,
          InputEmail: document.getElementById("contactEmail").value,
          InputPhone: document.getElementById("contactPhone").value
      };

      fetch("/LAMPAPI/Contacts.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(newContact)
      })
      .then(r => r.json())
      .then(data => {
          if (data.id > 0) {
              document.getElementById("addContactForm").style.display = "none";
              fetchContacts("");
              console.log("Here")
          } else {
            console.log("Bad")
              alert("Error: " + data.error);
          }
      })
      .catch(err => console.error("Error:", err));
  });
}

  function deleteContact(contactId){
      const user = readUser();
      if (!user) return;
      fetch("/LAMPAPI/Contacts.php", {
          method: "POST",
          headers: { "Content-Type": "application/json"},
          body: JSON.stringify({
              mode: 4,
              InputID: Number(contactId),
              id: user.id
          })
      })
      .then(r => r.json().catch(() => null))
      .then(data => {
          const contactsTableBody = document.getElementById("contactsTableBody");
          if (contactsTableBody) {
              contacts();
          }
      })
      .catch(err => console.error("Error deleting:", err));
  }

function editContact(contactId){
    const user = readUser();
    const newContact = {
        mode: 0,
        userId: user.id,
        contactId: contactId
    }
    fetch("/LAMPAPI/Contacts.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(newContact)
    })
    .then(r => r.json())
    .then(data => {
        console.log(data),
        document.getElementById("editContactFirstName").value = data["FirstName"],
        document.getElementById("editContactLastName").value = data["LastName"],
        document.getElementById("editContactPhone").value = data["Phone"],
        document.getElementById("editContactEmail").value = data["Email"]
    })
    document.getElementById("editContactForm").style.display = "block";
    window.location.href = "contacts.html";
}

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

  function readUser(){
      const c = document.cookie.split("; ").find(c => c.startsWith("user="));
      if(!c) {
          window.location.href = "login.html";
          return null;
      }
      try {
          return JSON.parse(decodeURIComponent(c.split("=")[1]));
      } catch {
          return null;
      }
  }
