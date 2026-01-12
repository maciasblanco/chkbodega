const $d = document;
const dropArea = document.querySelector(".drop-area");
const containerImg = document.querySelector(".container-img");
const fileContainer = document.querySelector(".file-container");
const dragText = dropArea.querySelector("h5");
const boton = dropArea.querySelector("#boton");
const input = dropArea.querySelector("#input-file");
const existImage = dropArea.querySelector("#existImage")
const preview =containerImg.querySelector('#preview');
const contadorArchivosSubidos=0;

let file;
let cargada=false;

boton.addEventListener('click', (e)=>{
    e.preventDefault();
    input.click();
});

input.addEventListener('change',(e)=>{
    file = input.files;
    dropArea.classList.add("active");
    showFiles(file);
    dropArea.classList.remove("active");
});  

dropArea.addEventListener("dragover",(e)=>{
    e.preventDefault();
    dropArea.classList.add("active");
    dragText.textContent="Suelta para subir los archivos";
});

dropArea.addEventListener("dragleave",(e)=>{
    e.preventDefault();
    dropArea.classList.remove("active");
    dragText.textContent="Arrastra y suelta la Imagen";
});

dropArea.addEventListener("drop",(e)=>{
    e.preventDefault();
    file = this.files;
    file= e.dataTransfer.files;
    showFiles(file);
    dropArea.classList.remove("active");
    dragText.textContent="Arrastra y suelta la Imagen";
});

function showFiles(file){
    if (file.length==1){
        if (file['0'].length === undefined){

            i="0";
            processFile(file,i);
        } 
    }
    else{
       alert ("Solo se puede caragar una Imagen por noticia");
    }
}
function processFile(file,indicador){

    const docType = file[indicador].type;
    const validExtensions = ['image/jpg','image/jpeg','image/png','image/gif'];

   
    
    if (validExtensions.includes(docType)){
        //archivo Valid0
        const fileReader = new FileReader();
        const id= `file-${Math.random().toString(32).substring(7)}`;
        
        
        if (cargada==false){
                fileReader.addEventListener('load',e=>{
                const fileUrl = fileReader.result;
                const image = `<div id ="${id}" class = "file-container">
                            <img src=${fileUrl} alt="${file[indicador].name}" width="125" height="125">-
                            <div class="status">
                                <span>identificador: ${id}</span>-
                                <span>${file[indicador].name}</span>-
                                <span class='status-text'>cargando........</span>
                            </div>
                            <div id="botonCerrar"><p>X</p></div>
                            <input type="button" id="cerrarImagen" hidden>  
                        </div>`;

                preview.innerHTML=image+preview.innerHTML;
                cerrarImagen(id);
                
            });
            
            fileReader.readAsDataURL(file[indicador]);
            
            uploadFile(file[indicador],id)
            //$(document.getElementById("#existImage")).value="Imagen Seleccionada";
            
            cargada=true;
        }
        else{
            alert("Solo puede cargar una imagen relacionada con la noticia");
        }
    }
    else{
        //mandar mensaje de archivo no valido
        alert('El archivo no es valido solo se aceptan imagenes con extenciones "jpg,jpeg,png,gif".');
    }

}

function uploadFile(file,id){
    
    const formData=new FormData();
    formData.append("file",file);
    formData.append("idImg",id);
    $.ajax(
        { 
            url:"upload",
            type:"POST",
            data:formData,
            cahe:false,
            contentType:false,
            processData:false,
            success:function(resp){
        
            }
        }
    ).done(function(dataResp){
        var datos = JSON.parse(dataResp);
        console.log(datos.id_imagen);
        $("#existImage").val(datos.id_imagen);
    }
    );

}

function cerrarImagen(id){

    const cerrarImagen= document.querySelector("#botonCerrar");
    cerrarImagen.addEventListener('click', (e)=>{
        
        alert("Se Eliminara la Imgen esta seguro de ello");
        document.getElementById(id).remove();
        cargada=false;
        //$(existImage).value="Sin imagen seleccionada";
        
        const formData=new FormData();
        //formData.append("file",file);
        formData.append("idImg",id);
            $.ajax(
            { 
                url:"delete-img",
                type:"POST",
                data:formData,
                cahe:false,
                contentType:false,
                processData:false,
                success:function(resp){
            
                }
            }
        ).done(function(dataResp){
            var datos = JSON.parse(dataResp);
            $("#existImage").val(datos.id_imagen);
        }
        );
    });

}
