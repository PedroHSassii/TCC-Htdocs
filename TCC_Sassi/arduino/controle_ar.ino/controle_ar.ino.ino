#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

const char* ssid = "KARIELY8907";
const char* password = "6z829>9B";

const String serverName = "http://192.168.137.1/dados.php";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  Serial.print("Conectando ao WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi conectado.");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;  // ← novo objeto WiFiClient
    HTTPClient http;

    http.begin(client, serverName); // ← forma nova e correta de usar

    int httpCode = http.GET();

    if (httpCode > 0) {
      Serial.printf("Código HTTP: %d\n", httpCode);
      String payload = http.getString();
      Serial.println("Resposta:");
      Serial.println(payload);
    } else {
      Serial.printf("Erro na requisição HTTP: %s\n", http.errorToString(httpCode).c_str());
    }

    http.end();
  } else {
    Serial.println("WiFi desconectado!");
  }

  delay(10000); // Espera 10 segundos
}

