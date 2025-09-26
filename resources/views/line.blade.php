<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>LIFF Demo</title>
  <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>
<body>
  <pre id="out">Loading…</pre>
  <script>
    const LIFF_ID = "{{ config('services.line.liff_id') }}"; // หรือใส่เป็นสตริงก็ได้

    async function main() {
      await liff.init({ liffId: LIFF_ID });

      // ถ้ายังไม่ login (เช่นเปิดนอก LINE)
      if (!liff.isLoggedIn()) {
        liff.login(); // จะเด้ง LINE Login
        return;
      }

      const out = document.getElementById('out');

      // 1) โปรไฟล์ (ใช้ได้เมื่อเปิด "ใน LINE")
      let profile = null;
      if (liff.isInClient()) {
        profile = await liff.getProfile(); // { userId, displayName, pictureUrl, statusMessage }
      }

      // 2) OIDC ID Token (ใช้ได้ทั้งใน/นอก LINE เพื่อยืนยันตัวตนฝั่งเซิร์ฟเวอร์)
      const idToken = liff.getIDToken(); // JWT
      const accessToken = liff.getAccessToken(); // สำหรับบาง API

      // ส่งไปหลังบ้านยืนยันและ upsert ผู้ใช้
      await fetch('/liff/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ idToken, profile })
      });

      out.textContent = JSON.stringify({ inClient: liff.isInClient(), profile, idTokenPresent: !!idToken }, null, 2);
    }

    main();
  </script>
</body>
</html>
