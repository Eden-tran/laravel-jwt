Front end: Vue
Back end: Laravel
API-> Authentication -> Token (JWT)
Request get Data: Gửi token thông qua header ( Authorization: Bearer <token>)
Server-> Kiểm tra token có hợp lên hay không -> Decode payload -> Truy vấn database trả về dữ liệu

## bảo mật Token

Access Token => nếu bị đánh cắp => Hacker khai thác dựa vào Token
-> giải pháp: hạ thấp thời gian sống token-> gây phiền phức cho người dùng
-> Cần bổ sung refresh token-> thời gian sống lâu hơn -> dùng để cấp lại Access token mới khi access token cũ hết hạn
-> khi logout -> thêm token vào blacklist -> Khi authorization -> cần kiểm tra token có trong blackist không

-   Tính hợp lệ
-   Thời gian sống
-   có trong blacklist không

# các vấn đề khi sử dụng refresh token (rt)

-   Refresh token hết hạn => client xử lý logout-> call api logout
-   Cấp lại access token mới bằng refresh token -> access token cũ vẫn hoạt động được. => giải pháp: khi cấp lại access token mới -> thêm access token cũ vào blacklist
