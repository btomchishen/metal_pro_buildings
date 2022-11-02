<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CJSCore::Init(array("jquery", "ajax"));
$APPLICATION->SetTitle('Leads updater');
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
        const ajaxUrl = '/lead_updater/lead_update.php';

        class LeadUpdater {
            constructor() {
                this.leadsCount = 0;
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

            setTotalPages(leadsCount) {
                this.totalPages = Math.ceil(leadsCount / this.getItemsPerPage());
            }

            setLeadsCount(leadsCount) {
                this.leadsCount = leadsCount;
            }

            setLastId(lastId) {
                this.lastId = lastId;
            }

            prepareStatus(status) {
                return status === 'Success' ? '<span style="color: green;">' + status + '</span>' : '<span style="color: red;">' + status + '</span>';
            }

            prepareLeadsInfo(operatedPages) {
                return 'Leads count: ' + this.leadsCount +
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
                        if (action === 'getLeadsCount')
                            $this.displayLeadsInfo(data);
                        if (action === 'leadsUpdate') {
                            $this.page++
                            $this.displayPageResult(data);
                        }
                    }
                }).responseText;

                return ajaxResult;
            }

            displayLeadsInfo(data) {
                let parsedData = JSON.parse(data);

                this.setLeadsCount(parsedData.leadsCount);
                this.setTotalPages(this.leadsCount);

                $('#info').html(this.prepareLeadsInfo(0));
            }

            displayPageResult(data) {
                let parsedData = JSON.parse(data);
                this.operatedPages++

                $('#resultTable').append(this.prepareRowForTable(parsedData));
                $('#info').html(this.prepareLeadsInfo(this.operatedPages));

                this.setLastId(parsedData.lastId);
                if (this.page <= this.totalPages) {
                    this.doAjax(this.getAjaxUrl(), 'leadsUpdate', true, {
                        page: this.page,
                        itemsPerPage: this.getItemsPerPage()
                    });
                }
            }
        }

        $(document).ready(function () {
            let leadUpdater = new LeadUpdater();

            leadUpdater.doAjax(leadUpdater.getAjaxUrl(), 'getLeadsCount', false);

            $('#updateButton').on('click', function (event) {
                leadUpdater.doAjax(leadUpdater.getAjaxUrl(), 'leadsUpdate', true, {
                    page: leadUpdater.page,
                    itemsPerPage: leadUpdater.getItemsPerPage()
                });
            })

            $('#stopButton').on('click', function (event) {
                leadUpdater.totalPages = leadUpdater.page;
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