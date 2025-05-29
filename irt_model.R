library(mirt)
library(readr)
library(jsonlite)

# Đọc dữ liệu
data <- read_csv("C:/Users/Hiep/OneDrive/Máy tính/Project_Web/Project_VueJS/ExamProject/ExamProject_Backend/responses.csv")

data <- read_csv("C:/Users/Hiep/.../responses.csv")

# Cắt bỏ user_id
response_only <- data[, -1]
response_only[is.na(response_only)] <- 0
response_only <- as.data.frame(sapply(response_only, as.numeric))

# Loại bỏ câu hỏi chỉ có 1 giá trị
response_only <- response_only[, sapply(response_only, function(col) length(unique(col)) > 1)]

if (nrow(response_only) < 3) {
    stop("Không đủ người làm bài để ước lượng mô hình.")
}

if (ncol(response_only) < 2) {
    stop("Không đủ câu hỏi phân biệt.")
}

# Ước lượng mô hình đơn giản hơn
model <- mirt(response_only, 1, itemtype = "2PL")  # hoặc "Rasch"

# Tính năng lực
theta <- fscores(model)

# Gộp với user_id
result <- data.frame(
  user_id = data[[1]],
  theta = theta[,1]
)
write_json(result, "C:/Users/Hiep/OneDrive/Máy tính/Project_Web/Project_VueJS/ExamProject/ExamProject_Backend/theta.json", pretty = TRUE)

