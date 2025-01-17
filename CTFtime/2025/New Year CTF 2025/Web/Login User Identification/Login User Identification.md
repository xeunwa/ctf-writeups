main page calls a javascript that checks https://ctf-spcs.mf.grsu.by/task/endp/script.js
`user === "FlagUser"`
```js
document.addEventListener("DOMContentLoaded", async () => {
  const output = document.getElementById("output");

  const response = await fetch("https://ctf-spcs.mf.grsu.by/task/endp/api/message");
});

document.getElementById("submit").addEventListener("click", async () => {
  const username = document.getElementById("username").value;
  const output = document.getElementById("output");

  if (username === "FlagUser") {
    output.innerText = "flag{api_of_a_healthy_person_?}";
  } else {
    output.innerText = "nothing";
  }
});
```