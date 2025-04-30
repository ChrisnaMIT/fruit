const boutons = document.querySelectorAll('.like')
boutons.forEach((bouton)=>{
    bouton.addEventListener('click', like)
})

function like(event)
{
    event.preventDefault()

    fetch(this.href).then(response=>response.json())
        .then((data) =>{
            this.querySelector('.nbrLikes').innerHTML = data.count

            if(data.isLiked){
                this.querySelector('.thumb').classList.remove('bi-hand-thumbs-up')
                this.querySelector('.thumb').classList.add('bi-hand-thumbs-up-fill')

            }else {
                this.querySelector('.thumb').classList.remove('bi-hand-thumbs-up-fill')
                this.querySelector('.thumb').classList.add('bi-hand-thumbs-up')

            }
        })
}