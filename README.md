# API-Web Page Acquisition Framework

## 项目简介
本项目是一个用于获取API小号的Web框架，支持从数据库中获取数据并记录访问信息。通过API接口，用户可以获取数据，同时系统会记录访问者的IP地址、访问时间以及访问次数。如果IP被封禁（`status = 1`），则无法获取数据。

## 项目提示
这个php项目不包含验证部分，请自行添加验证以及登录部分！

## 环境要求
- **MySQL**: 5.7.x
- **Nginx**: 1.15
- **PHP**: 7.3+

## 搭建步骤
1. **克隆项目**:
2. **配置数据库**:
   - 创建数据库并导入表结构（参考`create_tables.sql`）。
   - 修改`api.php`中的数据库配置：
     ```php
     $config = [
         'host'     => 'localhost',
         'dbname'   => 'your_database',
         'user'     => 'db_user',
         'password' => 'db_password'
     ];
     ```
3. **配置Web服务器**:
   - 将项目部署到Nginx或Apache服务器。
   - 确保PHP已安装并支持PDO扩展。

4. **访问API**:
   - 通过浏览器或工具访问`api.php`，例如：
     ```
     http://xxxx/api.php
     ```

## 错误代码说明
| status | 描述               |
|--------|--------------------|
| 1      | IP被封禁           |
| 2      | 没有可用数据       |
| 3      | 数据库错误         |
| 4      | 系统错误           |

## 示例响应
### 成功获取数据
json
{
"status": 0,
"data": "测试数据1"
}

### IP被封禁
json
{
"status": 1,
"message": "IP被封禁，无法获取数据"
}

### 没有可用数据
json
{
"status": 2,
"message": "没有可用数据"
}

### 数据库错误
json
{
"status": 3,
"message": "数据库错误: SQLSTATE[HY000]: General error: 1442 ..."
}

### 系统错误
json
{
"status": 4,
"message": "系统错误: Database connection failed"
}

## 构建数据库
在MySQL中执行以下SQL语句以创建所需的表结构：
```sql
CREATE TABLE user_data (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    IP VARCHAR(45) NOT NULL,
    get_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TINYINT DEFAULT 0,
    count INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE account (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    data VARCHAR(9999) NOT NULL,
    is_deleted TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 数据定制
如有其他问题，定制功能请联系：
- **QQ**: 1811144677
