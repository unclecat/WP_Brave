# 发布到 GitHub 指南

## 方法一：命令行发布（推荐）

### 1. 配置 Git 用户信息（首次使用）

```bash
git config --global user.name "你的GitHub用户名"
git config --global user.email "你的GitHub邮箱"
```

### 2. 在 GitHub 创建仓库

1. 登录 [GitHub](https://github.com)
2. 点击右上角 `+` → `New repository`
3. 填写信息：
   - Repository name: `brave-love`（或你喜欢的名字）
   - Description: `一个浪漫的 WordPress 情侣主题`
   - 选择 Public（公开）或 Private（私有）
   - 不要勾选 Initialize this repository with a README
4. 点击 `Create repository`

### 3. 推送代码到 GitHub

```bash
cd brave-wp

# 添加远程仓库（替换 yourname 为你的 GitHub 用户名）
git remote add origin https://github.com/yourname/brave-love.git

# 推送代码
git branch -M main
git push -u origin main
```

### 4. 创建 Release（发布版本）

```bash
# 创建标签
git tag -a v0.1 -m "Initial release v0.1"

# 推送标签
git push origin v0.1
```

或者在 GitHub 网页上操作：
1. 进入仓库 → Releases → Draft a new release
2. Choose a tag: `v0.1`
3. Release title: `Brave Love v0.1`
4. 填写发布说明
5. 点击 `Publish release`

---

## 方法二：GitHub Desktop（图形界面）

1. 下载 [GitHub Desktop](https://desktop.github.com/)
2. 登录你的 GitHub 账号
3. File → Add local repository → 选择 brave-wp 文件夹
4. 点击 `Publish repository`
5. 填写仓库名称和描述
6. 点击 `Publish Repository`

---

## 方法三：VS Code（编辑器内）

1. 打开 VS Code
2. 文件 → 打开文件夹 → 选择 brave-wp
3. 点击左侧源代码管理图标（Ctrl+Shift+G）
4. 点击「初始化仓库」（如未初始化）
5. 点击「...」→ 「远程」→ 「添加远程」
6. 输入 GitHub 仓库地址
7. 点击「推送」

---

## 验证发布

发布成功后，访问：
```
https://github.com/你的用户名/brave-love
```

你应该能看到：
- ✅ 所有主题文件
- ✅ README.md 渲染
- ✅ 提交历史
- ✅ Releases 页面

---

## 下载链接

发布后，用户可以通过以下方式下载：

1. **Git 克隆**：
   ```bash
   git clone https://github.com/yourname/brave-love.git
   ```

2. **下载 ZIP**：
   ```
   https://github.com/yourname/brave-love/archive/refs/heads/main.zip
   ```

3. **Release 下载**：
   ```
   https://github.com/yourname/brave-love/releases/download/v0.1/brave-love.zip
   ```
