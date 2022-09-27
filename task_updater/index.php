<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");


CJSCore::Init(array("jquery", "ajax"));
$APPLICATION->SetTitle('Tasks updater');
?>
    <div id="page"></div>
    <button id="btn">Update</button>
    <div id="result"></div>
    <script>
        let tasksCount = 0;
        let highestId = 0;
        let lastId = 0;
        let page = 1;
        let pagesCount = 0;
        let operatedPages = 0;
        let itemsPerPage = 100;

        window.addEventListener('load', (event) => {
            $.ajax({
                type: 'POST',
                url: '/task_updater/task_update.php',
                data: {
                    "action": "getTasksCount",
                },
                async: false,
                success: function (data) {
                    let parsedData = JSON.parse(data)
                    tasksCount = parsedData.tasksCount;

                    pagesCount = calculatePagesCount(tasksCount)

                    let pages = document.getElementById('page');
                    pages.innerHTML = 'Operated pages: ' + '0/' + pagesCount

                    BX.UI.Notification.Center.notify({
                        content: "Tasks count: " + parsedData.tasksCount,
                        position: "top-right"
                    });
                },
            })


            $.ajax({
                type: 'POST',
                url: '/task_updater/task_update.php',
                data: {
                    "action": "getHighestId",
                },
                async: false,
                success: function (data) {
                    let parsedData = JSON.parse(data)
                    highestId = parsedData.highestId;

                    BX.UI.Notification.Center.notify({
                        content: "Highest Id: " + parsedData.highestId,
                        position: "top-right"
                    });
                },
            })

            document.getElementById('btn').addEventListener('click', (event) => {
                let res = document.getElementById('result');
                let pages = document.getElementById('page');
                pagesCount = 3
                while (page <= pagesCount) {
                    var x = test()
                    console.log(x)

                    res.innerHTML += 'Page: ' + x.page + ', IDs: ' + lastId + '-' + x.lastId + ' - ' + status + '<br>'
                    pages.innerText = 'Operated pages: ' + operatedPages + '/' + pagesCount
                    page++;
                }

            })
        })

        function test() {
            var res = $.ajax({
                type: 'POST',
                url: '/task_updater/task_update.php',
                data: {
                    "action": "test",
                    "page": page,
                    "itemsPerPage": itemsPerPage
                },
                async: false,
                success: function (data) {
                    let parsedData = JSON.parse(data)
                    operatedPages++

                    let status = '';
                    switch (parsedData.status) {
                        case "Success":
                            status = '<span style="color: green;">' + parsedData.status + '</span>'
                            break;
                        default:
                            status = '<span style="color: red;">' + parsedData.status + '</span>'
                            break;
                    }

                    // res.innerHTML += 'Page: ' + parsedData.page + ', IDs: ' + lastId + '-' + parsedData.lastId + ' - ' + status + '<br>'
                    // pages.innerText = 'Operated pages: ' + operatedPages + '/' + pagesCount
                    lastId = parsedData.lastId
                },
            }).responseText

            return JSON.parse(res);
        }

        function calculatePagesCount(tasksCount) {
            return Math.ceil(tasksCount / itemsPerPage);
        }
    </script>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");