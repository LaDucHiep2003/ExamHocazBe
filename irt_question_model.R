library(mirt)
library(jsonlite)
library(readr)

data <- read_csv("C:/Users/Hiep/OneDrive/Máy tính/Project_Web/Project_VueJS/ExamProject/ExamProject_Backend/responses.csv")

response_only <- data[, -1]

# Loại bỏ câu hỏi vô giá trị (chỉ toàn 1 loại)
response_only <- response_only[, sapply(response_only, function(col) length(unique(col)) > 1)]

# Ước lượng mô hình 3PL
model <- mirt(response_only, 1, itemtype = "3PL")

# Tính năng lực thí sinh
theta <- fscores(model)

# Ghi theta ra file JSON
result <- data.frame(
    user_id = data[[1]],
    theta = theta[, 1]
)
write_json(result, "C:/Users/Hiep/OneDrive/Máy tính/Project_Web/Project_VueJS/ExamProject/ExamProject_Backend/theta.json", pretty = TRUE)

# Trích tham số câu hỏi (a, b, c)
item_params <- coef(model, IRTpars = TRUE, simplify = TRUE)$items
param_df <- data.frame(
    question_id = as.integer(gsub("Q", "", rownames(item_params))),
    a = item_params[, "a"],
    b = item_params[, "b"],
    c = item_params[, "g"]
)

write_json(param_df, "C:/Users/Hiep/OneDrive/Máy tính/Project_Web/Project_VueJS/ExamProject/ExamProject_Backend/item_parameters.json", pretty = TRUE)
