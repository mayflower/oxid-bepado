# Setup

1. Autoloader in Oxid index.php hinzufügen
2. $this->aUserComponents['mfcmp_bepado'] = 0
3. $this->iDebug = true

# Todos

1. PDO Config aus Oxid Konfiguration holen
2. API key konfigurierbar machen
3. Produkte an bepado exportieren mit $sdk->recordInsert($id); (Siehe docs/tutorial_export.md)
