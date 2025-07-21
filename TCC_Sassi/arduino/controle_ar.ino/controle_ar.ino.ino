#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <IRremote.h> // Biblioteca para controle IR

#define NUM_SALA 101
#define NUM_PREDIO 5

const char* ssid = "KARIELY8907";
const char* password = "6z829>9B";

const String serverName = "http://192.168.137.1/dados.php";

const int ledPin1 = D2;     // LED 1
const int ledPin2 = D3;     // LED 2
const int tsopPin = D4;     // Pino do receptor IR (TSOP4838)
const int ledIrPin = D5;    // Pino do LED Infrared (TX)
const int dhtPin = D6;      // Pino do sensor DHT11
const int dhtType = DHT11;  // Tipo do sensor

IRrecv irrecv(tsopPin);
decode_results results;

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  pinMode(ledPin1, OUTPUT);
  pinMode(ledPin2, OUTPUT);
  pinMode(tsopPin, INPUT);

  Serial.print("Conectando ao WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi conectado.");
  irrecv.enableIRIn(); // Inicia o receptor IR
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    String url = serverName + "?sala=" + String(NUM_SALA) + "&predio=" + String(NUM_PREDIO);
    http.begin(client, url);

    int httpCode = http.GET();

    if (httpCode > 0) {
      String payload = http.getString();
      Serial.println("Resposta:");
      Serial.println(payload);

      // Analisando a resposta JSON
      if (payload.indexOf("captacao") != -1) {
        captacao(); // Chama a função de captação
      } else if (payload.indexOf("atualiza") != -1) {
        atualiza(); // Chama a função de atualização
      } else {
        Serial.println("Nenhuma ação necessária.");
      }
    } else {
      Serial.printf("Erro na requisição HTTP: %s\n", http.errorToString(httpCode).c_str());
    }

    http.end();
  } else {
    Serial.println("WiFi desconectado!");
  }

  delay(10000); // Espera 10 segundos
}

void captacao() {
  Serial.println("Iniciando captação...");
  
  // Acende os LEDs
  digitalWrite(ledPin1, HIGH);
  digitalWrite(ledPin2, HIGH);

  // Aguarda a captura do código
  while (true) {
    if (irrecv.decode(&results)) {
      Serial.print("Código captado: ");
      Serial.println(results.value);

      // Salvar o código captado no banco de dados
      salvarCodigo(results.value);

      // Desliga os LEDs
      digitalWrite(ledPin1, LOW);
      digitalWrite(ledPin2, LOW);
      irrecv.resume(); // Prepara para a próxima leitura
      break; // Encerra a função de captação
    }
  }
}

void salvarCodigo(unsigned long codigo) {
  Serial.print("Salvando código no banco: ");
  Serial.println(codigo);
  
  // Monta a URL para salvar o código
  String url = "http://192.168.137.1/salvar_codigo.php?sala=" + String(NUM_SALA) + "&predio=" + String(NUM_PREDIO) + "&codigo=" + String(codigo);
  
  HTTPClient http;
  http.begin(url);
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("Resposta do servidor:");
    Serial.println(payload);
  } else {
    Serial.printf("Erro na requisição HTTP: %s\n", http.errorToString(httpCode).c_str());
  }
  
  http.end();
}

void atualiza() {
  Serial.println("Função de atualização chamada.");
  // Adicione aqui o código para a função de atualização
}
