<div>
    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <h3 class="box-title">Daily Report</h3>
            </div>
        </div>
        <div class="box-body">
            <p>{{ $report['title'] }}</p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px; border: solid 1px;">#</th>
                        <th style="border: solid 1px;">Name (Email)</th>
                        <th style="border: solid 1px;">To Do</th>
                        <th style="border: solid 1px;">Done Today</th>
                        <th style="border: solid 1px;">Fail Today</th>
                        <th style="border: solid 1px;">Daily Cap</th>
                        <th style="border: solid 1px;">Done Last 24 hs</th>
                        <th style="border: solid 1px;">Fail Last 24 hs</th>
                        <th style="border: solid 1px;">Last 7 days Done</th>
                        <th style="border: solid 1px;">Fail Last 7 days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['data'] as $line)
                        <tr>
                            <td style="border: solid 1px;">{{ $line->id }}</td>
                            <td style="border: solid 1px;">{{ $line->name }} ({{ $line->email }})</td>
                            <td style="border: solid 1px;">{{ $line->status_ready }}</td>
                            <td style="border: solid 1px;">{{ $line->today_status_done }}</td>
                            <td style="border: solid 1px;">{{ $line->today_status_fail }}</td>
                            <td style="border: solid 1px;">{{ $line->cap }}</td>
                            <td style="border: solid 1px;">{{ $line->l24h_status_done }}</td>
                            <td style="border: solid 1px;">{{ $line->l24h_status_fail }}</td>
                            <td style="border: solid 1px;">{{ $line->l7d_status_done }}</td>
                            <td style="border: solid 1px;">{{ $line->l7d_status_fail }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="overlay" v-if="loading">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
</div>