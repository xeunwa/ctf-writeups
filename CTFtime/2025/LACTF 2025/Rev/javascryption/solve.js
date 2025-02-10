function checkFlag(flag) {
    const step1 = btoa(flag);
    const step2 = step1.split("").reverse().join(""); //"aa aaa aaa"[]
    const step3 = step2.replaceAll("Z", "[OLD_DATA]");
    const step4 = encodeURIComponent(step3);
    const step5 = btoa(step4);
    return step5 === "JTNEJTNEUWZsSlglNUJPTERfREFUQSU1RG85MWNzeFdZMzlWZXNwbmVwSjMlNUJPTERfREFUQSU1RGY5bWI3JTVCT0xEX0RBVEElNURHZGpGR2I=";
}

function reversedCabin(b64) {
    const step1 = atob(b64) // string
    const step2 = decodeURIComponent(step1) // 
    const step3 = step2.replaceAll("[OLD_DATA]", "Z")
    const step4 = step3.split("").reverse().join("")
    const step5 = atob(step4)
    return step5
}

r = reversedCabin("JTNEJTNEUWZsSlglNUJPTERfREFUQSU1RG85MWNzeFdZMzlWZXNwbmVwSjMlNUJPTERfREFUQSU1RGY5bWI3JTVCT0xEX0RBVEElNURHZGpGR2I=")
console.log(r)