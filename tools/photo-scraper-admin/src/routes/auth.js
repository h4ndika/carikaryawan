import express from 'express';
import bcrypt from 'bcryptjs';
import db from '../db.js';
import { redirectIfAuth } from '../middleware/auth.js';

const router = express.Router();

// Login page
router.get('/login', redirectIfAuth, (req, res) => {
  res.render('login', { error: null });
});

// Login handler
router.post('/login', async (req, res) => {
  const { email, password } = req.body;

  try {
    const [users] = await db.query('SELECT * FROM users WHERE email = ?', [email]);

    if (users.length === 0) {
      return res.render('login', { error: 'Email atau password salah' });
    }

    const user = users[0];

    // Verify password
    const isValid = await bcrypt.compare(password, user.password);

    if (!isValid) {
      return res.render('login', { error: 'Email atau password salah' });
    }

    // Set session
    req.session.userId = user.id;
    req.session.user = {
      id: user.id,
      name: user.name,
      email: user.email,
    };

    res.redirect('/');
  } catch (error) {
    console.error('Login error:', error);
    res.render('login', { error: 'Terjadi kesalahan saat login' });
  }
});

// Logout
router.get('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Logout error:', err);
    }
    res.redirect('/login');
  });
});

export default router;
