<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CJSCore::Init(array("jquery", "ajax"));
$APPLICATION->SetTitle('Tasks updater');
?>
    <div id="info"></div>
    <button id="updateButton">Update</button>
    <table id="resultTable">
        <tr>
            <td>Page Number</td>
            <td>IDs Range</td>
            <td>Status</td>
            <td>Time processing</td>
        </tr>
    </table>
    </div>

    <script>
        const itemsPerPage = 100;
        const ajaxUrl = '/task_updater/task_update.php';

        let tasksCount = 0;
        let lastId = 0;
        let page = 1;
        let pagesCount = 0;
        let operatedPages = 0;

        $(document).ready(function () {
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: {
                    "action": "getTasksCount",
                },
                async: false,
                success: function (data) {
                    let parsedData = JSON.parse(data)
                    tasksCount = parsedData.tasksCount;

                    pagesCount = calculatePagesCount(tasksCount)

                    $('#info').html('Tasks count: ' + tasksCount + '<br>Operated pages: ' + '0/' + pagesCount)
                }
            })

            $('#updateButton').on('click', function (event) {
                updateTasks();
            })
        })

        function calculatePagesCount(tasksCount) {
            return Math.ceil(tasksCount / itemsPerPage);
        }

        function formatStatus(status) {
            if (status === 'Success')
                return '<span style="color: green;">' + status + '</span>'
            else
                return '<span style="color: red;">' + status + '</span>'
        }

        function prepareRowForTable(page, fromId, toId, status, time) {
            return '<tr>' +
                '<td>' + page + '</td>' +
                '<td>' + fromId + '-' + toId + '</td>' +
                '<td>' + status + '</td>' +
                '<td>' + time + '</td>' +
                '</tr>'
        }

        function updateTasks() {
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: {
                    "action": "tasksUpdate",
                    "page": page,
                    "itemsPerPage": itemsPerPage
                },
                async: true,
                success: function (data) {
                    let parsedData = JSON.parse(data)
                    operatedPages++

                    $('#resultTable').append(prepareRowForTable(parsedData.page, lastId, parsedData.lastId, formatStatus(parsedData.status), parsedData.time))
                    $('#info').html('Tasks count: ' + tasksCount + '<br>Operated pages: ' + operatedPages + '/' + pagesCount)

                    lastId = parsedData.lastId
                    if (page <= pagesCount) {
                        updateTasks();
                    }
                },
            })
            page++
        }
    </script>

    <style>
        #resultTable {
            width: 40%;
            text-align: center;
            border: 1px solid;
        }
    </style>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");