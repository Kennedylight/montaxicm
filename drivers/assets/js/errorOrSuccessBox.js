
let intE = -1,
	intS = -1;

function openError(i, j = 6000) {
	let errorMess = document.querySelector(".errorMess");
	if (i != '') errorMess.querySelector(".mess").innerHTML = i;
	let successMess = document.querySelector(".successMess");
	if (intE != -1) {
		clearTimeout(intE);
		intE = -1;
		if (errorMess.classList.contains("active"))
			errorMess.classList.remove("active");
	}
	if (successMess.classList.contains("active"))
		successMess.classList.remove("active");
	if (!errorMess.classList.contains("active"))
		errorMess.classList.add("active");
	intE = setTimeout(() => {
		errorMess.classList.remove("active");
		errorMess.querySelector(".mess").innerHTML = '';
	}, j);
}

function openSuccess(i = '', j = 6000) {
	let successMess = document.querySelector(".successMess");
	if (i != '') successMess.querySelector(".mess").innerHTML = i;
	let errorMess = document.querySelector(".errorMess");
	if (intS != -1) {
		clearTimeout(intS);
		intS = -1;
		if (successMess.classList.contains("active"))
			successMess.classList.remove("active");
	}
	if (errorMess.classList.contains("active"))
		errorMess.classList.remove("active");
	if (!successMess.classList.contains("active"))
		successMess.classList.add("active");
	intS = setTimeout(() => {
		successMess.classList.remove("active");
		successMess.querySelector(".mess").innerHTML = '';
	}, j);
}