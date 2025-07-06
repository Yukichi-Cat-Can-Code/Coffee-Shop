document.addEventListener("DOMContentLoaded", function () {
  // Kiểm tra nếu có element map trong trang
  var mapElement = document.getElementById("map");
  if (mapElement) {
    // Tọa độ TP HCM, Việt Nam (123 Nguyễn Huệ, Quận 1)
    var coffeeLocation = [10.773621, 106.703636];

    // Khởi tạo bản đồ với tọa độ và mức zoom
    var map = L.map("map").setView(coffeeLocation, 20);

    // Thêm layer OpenStreetMap
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Thêm marker cho quán cà phê
    var marker = L.marker(coffeeLocation).addTo(map);

    // Thêm popup cho marker
    marker
      .bindPopup(
        "<b>Artisan Coffee</b><br>123 Nguyễn Huệ, Quận 1<br>TP. Hồ Chí Minh"
      )
      .openPopup();
  }
});
