<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CJSCore::Init(array("jquery", "ajax"));
$APPLICATION->SetTitle('Tasks updater');
?>
    <div id="info"></div>
    <button id="updateButton">Update</button>
    <button id="stopButton">Stop script</button>
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

        class TaskUpdater {
            constructor() {
                this.tasksCount = 0;
                this.lastId = 0;
                this.page = 1;
                this.totalPages = 0;
                this.operatedPages = 0;
            }

            getAjaxUrl() {
                return ajaxUrl;
            }

            getItemsPerPage() {
                return itemsPerPage;
            }

            setTotalPages(tasksCount) {
                this.totalPages = Math.ceil(tasksCount / this.getItemsPerPage());
            }

            setTasksCount(tasksCount) {
                this.tasksCount = tasksCount;
            }

            setLastId(lastId) {
                this.lastId = lastId;
            }

            prepareStatus(status) {
                return status === 'Success' ? '<span style="color: green;">' + status + '</span>' : '<span style="color: red;">' + status + '</span>';
            }

            prepareTasksInfo(operatedPages) {
                return 'Tasks count: ' + this.tasksCount +
                    '<br>Operated pages: ' + operatedPages + '/' + this.totalPages;
            }

            prepareRowForTable(data) {
                return '<tr>' +
                    '<td>' + data.page + '</td>' +
                    '<td>' + this.lastId + '-' + data.lastId + '</td>' +
                    '<td>' + this.prepareStatus(data.status) + '</td>' +
                    '<td>' + data.time + '</td>' +
                    '</tr>';
            }

            doAjax(url, action, async, inputData = null) {
                let $this = this;
                let mergedData = {action, ...inputData};

                let ajaxResult = $.ajax({
                    type: 'POST',
                    url: url,
                    data: mergedData,
                    async: async,
                    success: function (data) {
                        if (action === 'getTasksCount')
                            $this.displayTasksInfo(data);
                        if (action === 'tasksUpdate') {
                            $this.page++
                            $this.displayPageResult(data);
                        }
                    }
                }).responseText;

                return ajaxResult;
            }

            displayTasksInfo(data) {
                let parsedData = JSON.parse(data);

                this.setTasksCount(parsedData.tasksCount);
                this.setTotalPages(this.tasksCount);

                $('#info').html(this.prepareTasksInfo(0));
            }

            displayPageResult(data) {
                let parsedData = JSON.parse(data);
                this.operatedPages++

                $('#resultTable').append(this.prepareRowForTable(parsedData));
                $('#info').html(this.prepareTasksInfo(this.operatedPages));

                this.setLastId(parsedData.lastId);
                if (this.page <= this.totalPages) {
                    this.doAjax(this.getAjaxUrl(), 'tasksUpdate', true, {
                        page: this.page,
                        itemsPerPage: this.getItemsPerPage()
                    });
                }
            }
        }

        $(document).ready(function () {
            let taskUpdater = new TaskUpdater();

            taskUpdater.doAjax(taskUpdater.getAjaxUrl(), 'getTasksCount', false);

            $('#updateButton').on('click', function (event) {
                taskUpdater.doAjax(taskUpdater.getAjaxUrl(), 'tasksUpdate', true, {
                    page: taskUpdater.page,
                    itemsPerPage: taskUpdater.getItemsPerPage()
                });
            })

            $('#stopButton').on('click', function (event) {
                taskUpdater.totalPages = taskUpdater.page;
            })
        })
    </script>

    <style>
        #resultTable {
            width: 40%;
            text-align: center;
            border: 1px solid;
        }
    </style>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");