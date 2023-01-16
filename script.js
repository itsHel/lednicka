'use strict';

// TODO picture

$(function(){
    const $confirm = $("#confirm-button");
    const $name = $("#name");
    const $bought = $("#bought");
    const $minExpire = $("#min-expire");
    // const $picture = $("#picture");
    const $tableBody = $("#table-body");

    const baseUrl = "php/index.php";

    getdata();

    $confirm.on("click", function(){
        if(!validate())
            return;

        let data = {type: "insert", data: {}};
        
        $(".form-input").each(function(){
            if($(this).val()){
                data.data[$(this).data("realname")] = $(this).val();
            }
        });

        console.log(data);

        $.ajax({
            url: baseUrl,
            method: "POST",
            data: data
        }).done(function(echo){
            if(echo == "success"){
                location.reload();
            } else {
                alert(echo);
            }
        }).catch(function(err){
            alert(err);
        });
    });

    $tableBody.on("click", function(e){
        if(e.target.classList.contains("delete-row")){
            let data = {type: "delete", id: e.target.dataset.id};

            $.ajax({
                url: baseUrl,
                method: "POST",
                data: data
            }).done(function(echo){
                if(echo == "success"){
                    location.reload();
                } else {
                    alert(echo);
                }
            }).catch(function(err){
                alert(err);
            });
        }
    });

    $tableBody.on("click", function(e){
        const node = e.target;

        if(node.nodeName == "TD" && !node.classList.contains("table-delete-row")){
            let newHtml = "<input value='" + node.textContent + "'>";

            node.innerHTML = newHtml;

            node.querySelector("input").focus();
        }
    });

    $tableBody.on("keypress", function(e){
        if(e.key == "Enter"){
            let node = e.target;
            let value = node.value;
            let id = node.closest("tr").dataset.id;
            let name = node.closest("td").dataset.name;

            let data = {
                type: "edit",
                data: {
                    id: id,
                    column: name,
                    value: value
                }
            }

            $.ajax({
                url: baseUrl,
                method: "POST",
                data: data
            }).done(function(echo){
                if(echo == "success"){
                    location.reload();
                } else {
                    alert(echo);
                }
            }).catch(function(err){
                alert(err);
            });
        }
    });

    function getdata(){
        let data = {type: "getdata"};
        
        $.ajax({
            url: baseUrl,
            method: "POST",
            data: data
        }).done(function(echo){
            createTable(JSON.parse(echo));
        }).catch(function(err){
            alert(err);
        });
    }

    function createTable(data){
        const date = new Date().toLocaleString("sv");

        let html = "";

        data.forEach(row => {
            let rowClass = "class=";
            let expire = (row.expire) ? row.expire : row.minExpire;

            if(expire < date){
                rowClass += "red";
            } else if(row.PS < date){
                rowClass += "yellow";
            }

            let rowHtml = "<tr data-id='" + row.id + "' " + ((rowClass) ? rowClass : "") + ">";

            for(const property in row){
                if(property != "id" && property != "PS"){
                    rowHtml += "<td class='table-" + property + "' data-name='" + property + "'>" + ((row[property]) ? row[property] : "") + "</td>";
                }
            }

            rowHtml += "<td class='table-delete-row'><button data-id='" + row.id + "' class='delete-row'>Smazat</button></td></tr>";

            html += rowHtml;
        });

        $tableBody.html(html);
    }

    function validate(){
        const mandatory = [$name, $bought, $minExpire];
        
        for(let i = 0; i < mandatory.length; i++){
            if(!mandatory[i].val()){
                alert("Chybí " + mandatory[i].data("showname"));
                return false;
            }
        }

        return true;
    }
});
