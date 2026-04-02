(() => {
  const root = document.getElementById("qrLiveRoot");
  const publicRoot = document.getElementById("attendanceQrFrame");
  const publicCanvas = document.getElementById("attendanceQrCanvas");
  const publicCountdown = document.getElementById("attendanceQrCountdown");

  if ((!root && !publicRoot) || typeof QRCode === "undefined") {
    return;
  }

  const endpoint = root ? root.dataset.endpoint || "api/qr/current" : null;
  const refreshSeconds = Number((root && root.dataset.refreshSeconds) || 30);
  const qrCanvas = root ? document.getElementById("qrCanvas") : publicCanvas;
  const qrUrl = root ? document.getElementById("qrUrl") : null;
  const qrCountdown = root
    ? document.getElementById("qrCountdown")
    : publicCountdown;
  const publicCurrentUrl = publicRoot
    ? publicRoot.dataset.currentUrl || ""
    : "";
  const publicCurrentExpiresAt = publicRoot
    ? publicRoot.dataset.currentExpiresAt || ""
    : "";
  let countdownTimer = null;

  function clearQr() {
    qrCanvas.innerHTML = "";
  }

  function renderQr(url) {
    clearQr();
    new QRCode(qrCanvas, {
      text: url,
      width: 240,
      height: 240,
      colorDark: "#1f2937",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.M,
    });

    if (qrUrl) {
      qrUrl.textContent = url;
    }
  }

  function startCountdown(target) {
    if (countdownTimer) {
      clearInterval(countdownTimer);
    }

    countdownTimer = setInterval(() => {
      const remaining = Math.max(0, Math.floor((target - Date.now()) / 1000));
      qrCountdown.textContent = `${remaining}s`;
      if (remaining <= 0) {
        loadToken();
      }
    }, 1000);
  }

  async function loadToken() {
    try {
      if (!endpoint) {
        return;
      }

      const response = await fetch(endpoint, {
        headers: { Accept: "application/json" },
      });
      const payload = await response.json();

      if (!payload.ok) {
        if (qrUrl) {
          qrUrl.textContent = "No fue posible obtener el QR.";
        }
        return;
      }

      renderQr(payload.url);
      const expiresAt = new Date(payload.expires_at).getTime();
      startCountdown(expiresAt);
    } catch (error) {
      if (qrUrl) {
        qrUrl.textContent = "Error cargando el QR.";
      }
    }
  }

  if (publicRoot && publicCurrentUrl) {
    renderQr(publicCurrentUrl);

    const expiresAt = publicCurrentExpiresAt
      ? new Date(publicCurrentExpiresAt).getTime()
      : 0;
    if (expiresAt > 0) {
      startCountdown(expiresAt);
    }
  }

  if (endpoint) {
    loadToken();
    setInterval(loadToken, refreshSeconds * 1000);
  }
})();
