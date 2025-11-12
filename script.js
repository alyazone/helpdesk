document.addEventListener("DOMContentLoaded", () => {
  // File upload functionality
  const fileUploadArea = document.getElementById("fileUploadArea")
  const fileInput = document.getElementById("fileInput")
  const fileList = document.getElementById("fileList")
  let uploadedFiles = []

  // Click to upload
  fileUploadArea.addEventListener("click", () => {
    fileInput.click()
  })

  // Drag and drop functionality
  fileUploadArea.addEventListener("dragover", (e) => {
    e.preventDefault()
    fileUploadArea.classList.add("dragover")
  })

  fileUploadArea.addEventListener("dragleave", () => {
    fileUploadArea.classList.remove("dragover")
  })

  fileUploadArea.addEventListener("drop", (e) => {
    e.preventDefault()
    fileUploadArea.classList.remove("dragover")
    const files = Array.from(e.dataTransfer.files)
    handleFiles(files)
  })

  // File input change
  fileInput.addEventListener("change", (e) => {
    const files = Array.from(e.target.files)
    handleFiles(files)
  })

  function handleFiles(files) {
    files.forEach((file) => {
      if (file.type.startsWith("image/")) {
        uploadedFiles.push(file)
        displayFile(file)
      }
    })
  }

  function displayFile(file) {
    const fileItem = document.createElement("div")
    fileItem.className = "file-item"

    fileItem.innerHTML = `
            <div class="file-info">
                <i class="fas fa-image"></i>
                <span>${file.name}</span>
                <small>(${formatFileSize(file.size)})</small>
            </div>
            <button type="button" class="file-remove" onclick="removeFile('${file.name}')">
                <i class="fas fa-times"></i>
            </button>
        `

    fileList.appendChild(fileItem)
  }

  function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
  }

  // Remove file function (global scope)
  window.removeFile = (fileName) => {
    uploadedFiles = uploadedFiles.filter((file) => file.name !== fileName)
    const fileItems = fileList.querySelectorAll(".file-item")
    fileItems.forEach((item) => {
      if (item.querySelector("span").textContent === fileName) {
        item.remove()
      }
    })
  }

  // Toolbar functionality
  const toolbarButtons = document.querySelectorAll(".toolbar-btn")
  const textarea = document.getElementById("keterangan")

  toolbarButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault()
      const icon = button.querySelector("i")

      if (icon.classList.contains("fa-bold")) {
        insertText("**", "**")
      } else if (icon.classList.contains("fa-italic")) {
        insertText("*", "*")
      } else if (icon.classList.contains("fa-underline")) {
        insertText("_", "_")
      } else if (icon.classList.contains("fa-list-ul")) {
        insertText("\n- ", "")
      } else if (icon.classList.contains("fa-list-ol")) {
        insertText("\n1. ", "")
      } else if (icon.classList.contains("fa-link")) {
        insertText("[", "](url)")
      }
    })
  })

  function insertText(before, after) {
    const start = textarea.selectionStart
    const end = textarea.selectionEnd
    const selectedText = textarea.value.substring(start, end)
    const newText = before + selectedText + after

    textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end)
    textarea.focus()
    textarea.setSelectionRange(start + before.length, start + before.length + selectedText.length)
  }

  // Form validation
  const form = document.getElementById("complaintForm")

  form.addEventListener("submit", (e) => {
    e.preventDefault()

    // Basic validation
    const requiredFields = form.querySelectorAll('[required], .form-input[data-required="true"]')
    let isValid = true

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.style.borderColor = "#ef4444"
        isValid = false
      } else {
        field.style.borderColor = "#d1d5db"
      }
    })

    // Check if at least one damage type is selected
    const damageCheckboxes = document.querySelectorAll('input[name="kerosakan[]"]')
    const isDamageSelected = Array.from(damageCheckboxes).some((cb) => cb.checked)

    if (!isDamageSelected) {
      alert("Sila pilih sekurang-kurangnya satu jenis kerosakan.")
      isValid = false
    }

    if (isValid) {
      // Show success message
      alert("Aduan/Cadangan telah berjaya dihantar!")

      // In a real application, you would submit the form data to a server
      console.log("Form data:", new FormData(form))
      console.log("Uploaded files:", uploadedFiles)
    } else {
      alert("Sila lengkapkan semua medan yang diperlukan.")
    }
  })

  // Reset form
  form.addEventListener("reset", () => {
    uploadedFiles = []
    fileList.innerHTML = ""

    // Reset custom styling
    const inputs = form.querySelectorAll(".form-input, .form-select, .form-textarea")
    inputs.forEach((input) => {
      input.style.borderColor = "#d1d5db"
    })
  })

  // Add dynamic behavior for asset registration
  const addButton = document.querySelector(".btn-add")
  addButton.addEventListener("click", () => {
    const input = document.getElementById("noPendaftaran")
    if (input.value.trim()) {
      // In a real application, you would add this to a list
      alert('Nombor pendaftaran "' + input.value + '" telah ditambah.')
      input.value = ""
    }
  })

  // Email validation for government domain
  const emailInput = document.getElementById("emel")
  emailInput.addEventListener("blur", function () {
    const email = this.value
    if (email && !email.includes("jpbdselangor.gov.my")) {
      this.style.borderColor = "#ef4444"
      alert("Hanya alamat emel jabatan sahaja yang dibenarkan.")
    } else {
      this.style.borderColor = "#d1d5db"
    }
  })
})
