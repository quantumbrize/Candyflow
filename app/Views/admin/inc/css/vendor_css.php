<style>
    .vendor_tr:hover{
        cursor: pointer;
        background-color: rgba(0,0,0,0.2);

    }
    

    .container {
        background-color: #ffffff;
        width: 60%;
        min-width: 450px;
        position: relative;
        margin: 50px auto;
        padding: 50px 20px;
        border-radius: 7px;
        box-shadow: 0 20px 35px rgba(0, 0, 0, 0.05);
    }

    input[type="file"] {
        display: none;
    }

    #btn_upload1 {
        display: block;
        position: relative;
        color: #ffffff;
        font-size: 18px;
        text-align: center;
        margin: auto;
        border-radius: 5px;
        cursor: pointer;
    }
    #btn_upload2 {
        display: block;
        position: relative;
        color: #ffffff;
        font-size: 18px;
        text-align: center;
        margin: auto;
        border-radius: 5px;
        cursor: pointer;
    }

    .container p {
        text-align: center;
        margin: 20px 0 30px 0;
    }

    #images {
        width: 90%;
        position: relative;
        margin: auto;
        display: flex;
        justify-content: space-evenly;
        gap: 20px;
        flex-wrap: wrap;
    }

    figure {
        width: 45%;
    }

    img {
        width: 100%;
    }

    figcaption {
        text-align: center;
        font-size: 2.4vmin;
        margin-top: 0.5vmin;
    }

    .user_documents{
        max-width:100px;
        max-height:200px;
        object-fit: cover;
    }
    .dt-column-title{
        display: block;
        width: max-content !important;
    }
    th{
        /* width: fit-content; */
        /* display: inline; */

    }
</style>