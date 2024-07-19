<!DOCTYPE html>
<html>

<head>
    <title>Danh Sách Điểm Danh Môn Học</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        h1 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        h2 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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
    <h1>Danh Sách Điểm Danh Môn {{ $ten_mh['ten_mh'] }}</h1>
    <h2>Nhóm môn học: {{ $nmh['nmh'] }}</h2>
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã SV</th>
                <th>Tên SV</th>
                <th>Tên lớp</th>
                <th>Số buổi học</th>
                <th>Số buổi điểm danh</th>
                <th>Số buổi vắng</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 1; @endphp
            @foreach($sinhviens as $sinhvien)
            <tr>
                <td>{{$stt}}</td>
                <td>{{ $sinhvien['ma_sv'] }}</td>
                <td>{{ $sinhvien['ten_sv'] }}</td>
                <td>{{ $sinhvien['ma_lop'] }}</td>
                <td>{{ $sinhvien['sbh'] }}</td>
                <td>{{ $sinhvien['sbdd'] }}</td>
                <td>{{ $sinhvien['sbv'] }}</td>
                <td>
                    @if ($sinhvien['sbv'] > $sinhvien['sbh'] / 2)
                    Cấm thi
                    @endif
                </td>
            </tr>
            @php $stt++; @endphp
            @endforeach
        </tbody>
    </table>
</body>

</html>