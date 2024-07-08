<!DOCTYPE html>
<html>
<head>
    <title>Danh Sách Điểm Danh Môn Lập trình Web</title>
    <style>
        @font-face {
            font-family: 'DejaVuSans','DejaVuSans-BoldOblique';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            src: url('{{ storage_path('fonts/DejaVuSans-BoldOblique.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'DejaVuSans', sans-serif;
        }

        h1 {
            font-family: 'DejaVuSans-BoldOblique', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Danh Sách Điểm Danh Môn Lập trình Web</h1>
    <table>
        <thead>
            <tr>
                <th>Mã SV</th>
                <th>Tên SV</th>
                <th>Số buổi học</th>
                <th>Số buổi điểm danh</th>
                <th>Số buổi vắng</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sinhviens as $sinhvien)
            <tr>
                <td>{{ $sinhvien['ma_sv'] }}</td>
                <td>{{ $sinhvien['ten_sv'] }}</td>
                <td>{{ $sinhvien['sbh'] }}</td>
                <td>{{ $sinhvien['sbdd'] }}</td>
                <td>{{ $sinhvien['sbv'] }}</td>
                <td>
                    @if ($sinhvien['sbv'] > $sinhvien['sbh'] / 2)
                        Cấm thi
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
