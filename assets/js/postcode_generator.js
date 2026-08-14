

/*==============================================================
//   Postcode Generator                                       //
=============================================================*/   
function displayCode() {
  const code = Math.floor(1000 + Math.random() * 9000);
  document.getElementById("postcode").value = code;
  checkPostcode(code);
}


/*==============================================================
//   postcode checker                                         //
=============================================================*/   
async function checkPostcode(code) {
  const alertBox = document.getElementById("postcode-alert");

  if (code >= 1000 && code <= 9999) {
    const res = await fetch(`add_user_account.php?action=verify_postcode&code=${code}`);
    const status = await res.text();

    if (status.trim() === "taken") {
      alertBox.textContent = `Used`;
      alertBox.style.color = "red";
      alertBox.style.display = "inline-block";
      return;
    }
  }
  alertBox.style.display = "none"; 
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("postcode")?.addEventListener("input", (e) => {
    if (e.target.value.length === 4) {
      checkPostcode(parseInt(e.target.value, 10));
    } else {
      document.getElementById("postcode-alert").style.display = "none";
    }
  });
});