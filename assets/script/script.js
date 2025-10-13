
const title_1 = document.querySelector(".faq_div_block_h2_1")
const title_2 = document.querySelector(".faq_div_block_h2_2")
const title_3 = document.querySelector(".faq_div_block_h2_3")
const title_4 = document.querySelector(".faq_div_block_h2_4")

const closed1 = document.querySelector(".closed1")
const closed2 = document.querySelector(".closed2")
const closed3 = document.querySelector(".closed3")
const closed4 = document.querySelector(".closed4")

closed1.style.display = 'none'
closed2.style.display = 'none'
closed3.style.display = 'none'
closed4.style.display = 'none'

let count1 = 0;
let count2 = 0;
let count3 = 0;
let count4 = 0;


title_1.addEventListener('click' , function(){
    if(count1 % 2 === 0){
        closed1.style.display = 'block'
        count1++
    }
    else {
        closed1.style.display = 'none'
        count1++
    }


})
title_2.addEventListener('click' , function(){
    if(count2 % 2 === 0){
        closed2.style.display = 'block'
        count2++
    }
    else {
        closed2.style.display = 'none'
        count2++
    }
})
title_3.addEventListener('click' , function(){
    if(count3 % 2 === 0){
        closed3.style.display = 'block'
        count3++
    }
    else {
        closed3.style.display = 'none'
        count3++
    }
})
title_4.addEventListener('click' , function(){
    if(count4 % 2 === 0){
        closed4.style.display = 'block'
        count4++
    }
    else {
        closed4.style.display = 'none'
        count4++
    }
})

